<?php
declare(strict_types=1);

require __DIR__ . '/support/content-validator.php';

function loadContent(): array
{
    $data = [];
    foreach (DATASETS as $dataset) {
        $data[$dataset] = decodeDataset($dataset, file_get_contents(ROOT . "/app/data/$dataset.json"));
    }
    return $data;
}

function contentTest(string $name, callable $check): void
{
    $check();
    echo "PASS $name\n";
}

function requireDiagnostic(array $errors, string $needle): void
{
    foreach ($errors as $error) {
        if (str_contains($error, $needle)) {
            return;
        }
    }
    throw new RuntimeException("Missing diagnostic '$needle'. Actual: " . implode("\n", $errors));
}

try {
    contentTest('current content', static function (): void {
        $errors = (new ContentValidator())->validate(loadContent());
        if ($errors) {
            throw new RuntimeException(implode("\n", $errors));
        }
    });

    $cases = [
        'missing title' => ['services.json[bankowosc-finanse].title: expected a non-blank string, got null', static function ($data) { unset($data['services'][0]->title); }],
        'wrong field type' => ['team.json[0].role: expected a non-blank string, got array', static function ($data) { $data['team'][0]->role = []; }],
        'invalid date' => ['.date: expected a valid YYYY-MM-DD date', static function ($data) { $data['articles'][0]->date = '2026-02-31'; }],
        'duplicate slug' => ['.slug: expected a unique slug', static function (&$data) { $data['articles'][] = clone $data['articles'][0]; }],
        'reserved slug' => ['articles.json[kontakt].slug: expected a non-reserved slug', static function ($data) { $data['articles'][0]->slug = 'kontakt'; }],
        'missing image' => ['.image: expected an existing, exact-case local asset', static function ($data) { $data['articles'][0]->image = 'missing-fixture.webp'; }],
        'wrong-case asset' => ['.portrait: expected an existing, exact-case local asset', static function ($data) { $data['team'][0]->portrait = 'OFFICE-RECEPTION-HERO.jpg'; }],
        'unsupported URL scheme' => ['.url: expected an allowed URL', static function ($data) { $data['site']->url = 'ftp://example.test'; }],
        'invalid vacancy status' => ['.status: expected open, closed or draft', static function ($data) { $data['recruitment']->vacancies[] = (object) ['status' => 'unknown']; }],
        'missing article body' => ['.body_html: expected a non-blank string, got null', static function ($data) { unset($data['articles'][0]->body_html); }],
        'broken inline asset' => ['.body_html.src: expected an existing, exact-case local asset', static function ($data) { $data['articles'][0]->body_html = '<p>Fixture</p><img src="/assets/img/missing.png">'; }],
    ];
    foreach ($cases as $name => [$needle, $mutate]) {
        contentTest($name, static function () use ($mutate, $needle): void {
            $data = loadContent();
            $mutate($data);
            requireDiagnostic((new ContentValidator())->validate($data), $needle);
        });
    }
    contentTest('malformed JSON', static function (): void {
        try {
            decodeDataset('fixture', '{"bad":');
        } catch (RuntimeException $e) {
            requireDiagnostic([$e->getMessage()], 'fixture.json: invalid JSON');
            return;
        }
        throw new RuntimeException('Malformed JSON was accepted.');
    });
    echo 'Content checks passed (' . (count($cases) + 2) . " cases).\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
