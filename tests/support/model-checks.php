<?php

declare(strict_types=1);

use App\Content\ArticleStatus;
use App\Models\Article;
use App\Models\JobOffer;
use App\Models\Specialization;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

if (getenv('APP_ENV') !== 'testing' || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_USERNAME') !== 'kjp_test'
    || ! preg_match('/\Akancelaria_jp_test_[a-f0-9]{16}\z/', getenv('DB_DATABASE') ?: '')) {
    throw new RuntimeException('This command requires a disposable test database.');
}

$base = $argv[1];
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

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
