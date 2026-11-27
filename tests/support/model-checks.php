<?php

declare(strict_types=1);

use App\Actions\ManageContent;
use App\Content\ArticleStatus;
use App\Content\ContentImporter;
use App\Models\Article;
use App\Models\JobOffer;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/bootstrap-test.php';

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function denied(callable $operation): void
{
    try {
        $operation();
    } catch (ValidationException) {
        return;
    }

    throw new RuntimeException('Expected Laravel validation to reject the operation.');
}

$conn = DB::connection();
check($conn->getDriverName() === 'mariadb', 'Wrong Laravel database driver.');
check($conn->selectOne('SELECT @@session.time_zone AS zone')->zone === '+00:00', 'Wrong session timezone.');
check($conn->getPdo()->getAttribute(PDO::ATTR_EMULATE_PREPARES) === false, 'Expected native prepared statements.');

DB::beginTransaction();
try {
    denied(fn () => Article::create(['title' => 'Fixture', 'slug' => 'admin', 'body_html' => '<p>Fixture</p>']));
    denied(fn () => Specialization::create(['slug' => 'Invalid URI']));
    denied(fn () => JobOffer::create(['status' => 'open', 'display_order' => 1]));
    $article = Article::create(['slug' => 'model-test-draft', 'title' => 'Fixture', 'body_html' => '<p>Fixture</p>', 'date' => '2024-02-29']);
    check($article->date->format('Y-m-d') === '2024-02-29', 'Date cast changed the publication day.');
    check($article->status === ArticleStatus::Draft, 'Unexpected draft state.');
    $article->update(['slug' => 'model-test-draft-edited', 'status' => ArticleStatus::Published]);
    denied(fn () => $article->update(['slug' => 'model-test-forbidden']));
    $article->refresh();
    $article->update(['status' => ArticleStatus::Unpublished]);
    denied(fn () => $article->update(['status' => ArticleStatus::Draft]));
    $article->refresh();
    $article->update(['status' => ArticleStatus::Published]);
    $article->delete();
    check(! Article::published()->whereKey($article->id)->exists(), 'Deleted article visible.');
    try {
        Article::create(['slug' => 'model-test-draft-edited', 'title' => 'Duplicate', 'body_html' => '<p>Fixture</p>']);
        throw new RuntimeException('Published slug was reused.');
    } catch (QueryException $exception) {
        check((int) $exception->errorInfo[1] === 1062, 'Expected duplicate-slug constraint.');
    }
    check($article->restore() && Article::published()->whereKey($article->id)->exists(), 'Article restore failed.');
} finally {
    DB::rollBack();
}

echo "Model constraints, slug stability and soft deletion passed.\n";

$observer = DB::build(['name' => 'content-lock-observer'] + $conn->getConfig());
$lock = 'kjp-content-'.substr(hash('sha256', $conn->getDatabaseName()), 0, 40);
check((int) $observer->selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$lock])->acquired === 1, 'Test could not acquire the content lock.');
try {
    check(Artisan::call('site:import-content') === 1 && str_contains(Artisan::output(), 'Another import is running.'),
        'Importer did not report a busy CMS lock.');
    Auth::setUser((new User)->forceFill(['is_active' => true, 'is_admin' => true]));
    try {
        app(ManageContent::class)->create(Specialization::class, fn () => throw new RuntimeException('CMS write ran while content was locked.'));
        throw new RuntimeException('CMS did not report a busy import lock.');
    } catch (ValidationException $exception) {
        check(isset($exception->errors()['data']), 'Busy CMS lock lost its form diagnostic.');
    }
} finally {
    $observer->selectOne('SELECT RELEASE_LOCK(?)', [$lock]);
}

try {
    app(ContentImporter::class)->import([Article::class => []]);
    throw new RuntimeException('Expected import refusal for populated content.');
} catch (RuntimeException $exception) {
    check(str_contains($exception->getMessage(), 'already populated'), 'Unexpected import failure.');
}
check((int) $observer->selectOne('SELECT IS_FREE_LOCK(?) AS available', [$lock])->available === 1,
    'Failed import retained the content lock.');
echo "Shared content lock contention and release passed.\n";
