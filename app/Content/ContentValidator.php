<?php

declare(strict_types=1);

namespace App\Content;

use App\Support\PublicUrl;
use DOMDocument;
use DOMXPath;
use stdClass;

final class ContentValidator
{
    private array $errors = [];

    public function __construct(private readonly string $publicDirectory) {}

    private function expect(bool $valid, string $field, string $expected, mixed $actual): bool
    {
        if (!$valid) {
            $detail = is_scalar($actual)
                ? json_encode(is_string($actual) ? substr($actual, 0, 120) : $actual, JSON_INVALID_UTF8_SUBSTITUTE)
                : get_debug_type($actual);
            $this->errors[] = "$field: expected $expected, got $detail";
        }
        return $valid;
    }

    private function fields(object $record, string $path, array $required, array $optional = []): void
    {
        foreach ($required as $key) {
            $value = $record->$key ?? null;
            $this->expect(is_string($value) && trim($value) !== '', "$path.$key", 'a non-blank string', $value);
        }
        foreach ($optional as $key) {
            if (property_exists($record, $key)) {
                $this->expect(is_string($record->$key), "$path.$key", 'a string', $record->$key);
            }
        }
    }

    private function lengths(object $record, string $path, array $varchar, array $text = []): void
    {
        foreach ($varchar as $key) {
            $value = $record->$key ?? null;
            if (is_string($value)) {
                $this->expect(mb_strlen($value, 'UTF-8') <= 255, "$path.$key", 'at most 255 characters', $value);
            }
        }
        foreach ($text as $key) {
            $value = $record->$key ?? null;
            if (is_string($value)) {
                $this->expect(strlen($value) <= 65535, "$path.$key", 'at most 65535 UTF-8 bytes', $value);
            }
        }
    }

    private function strings(mixed $value, string $path, bool $nonEmpty = false): void
    {
        if (!$this->expect(is_array($value) && (!$nonEmpty || count($value) > 0), $path, $nonEmpty ? 'a non-empty string list' : 'a string list', $value)) {
            return;
        }
        foreach ($value as $index => $item) {
            $this->expect(is_string($item) && trim($item) !== '', "{$path}[$index]", 'a non-blank string', $item);
        }
    }

    private function records(mixed $value, string $path): iterable
    {
        if ($this->expect(is_array($value), $path, 'a list of records', $value)) {
            foreach ($value as $index => $record) {
                $label = is_string($record->slug ?? null) ? $record->slug : $index;
                $field = "{$path}[$label]";
                if ($this->expect($record instanceof stdClass, $field, 'an object', $record)) {
                    yield $field => $record;
                }
            }
        }
    }

    private function slugs(iterable $records, array $reserved = []): void
    {
        $seen = [];
        foreach ($records as $path => $record) {
            $slug = $record->slug ?? null;
            if ($this->expect(PublicUrl::isSlug($slug), "$path.slug", 'a lowercase route slug of at most 191 characters', $slug)) {
                $this->expect(!isset($seen[$slug]), "$path.slug", 'a unique slug', $slug);
                $this->expect(!in_array($slug, $reserved, true), "$path.slug", 'a non-reserved slug', $slug);
                $seen[$slug] = true;
            }
        }
    }

    private function url(mixed $value, string $path, bool $allowLocal = true, bool $allowContact = false): bool
    {
        $valid = is_string($value) && (
            (preg_match('~^https?://~i', $value) && filter_var($value, FILTER_VALIDATE_URL))
            || ($allowLocal && preg_match('~^/(?!/)[^\x00-\x20\\\\]*$~D', $value))
            || ($allowContact && preg_match('~^(?:#[^\s]*|mailto:[^\s]+|tel:[+0-9-]+)$~iD', $value))
        );
        return $this->expect($valid, $path, 'an allowed URL (HTTP(S)' . ($allowLocal ? ' or a local path' : '') . ($allowContact ? ', fragment, mailto or tel' : '') . ')', $value);
    }

    private function asset(string $url, string $path): void
    {
        $relative = rawurldecode((string) parse_url($url, PHP_URL_PATH));
        $dir = $this->publicDirectory;
        foreach (explode('/', ltrim($relative, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..' || !is_dir($dir) || !in_array($part, scandir($dir), true)) {
                $this->expect(false, $path, 'an existing, exact-case local asset', $url);
                return;
            }
            $dir .= '/' . $part;
        }
        $this->expect(is_file($dir), $path, 'a local asset file', $url);
    }

    private function image(mixed $value, string $path): void
    {
        if ($this->expect(is_string($value) && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $value) === 1, $path, 'an image filename', $value)) {
            $this->asset('/assets/img/' . $value, $path);
        }
    }

    private function articleMarkup(string $html, string $path): void
    {
        $doc = new DOMDocument();
        $doc->loadHTML('<meta charset="utf-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach ((new DOMXPath($doc))->query('//*[@href or @src or @srcset]') as $node) {
            foreach (['href', 'src', 'srcset'] as $attribute) {
                if (!$node->hasAttribute($attribute)) {
                    continue;
                }
                $raw = $node->getAttribute($attribute);
                $urls = $attribute === 'srcset'
                    ? array_map(static fn (string $candidate): string => preg_split('/\s+/', trim($candidate))[0], explode(',', $raw))
                    : [$raw];
                foreach ($urls as $url) {
                    $field = "$path.$attribute";
                    if ($this->url($url, $field, true, $attribute === 'href') && str_starts_with($url, '/')
                        && ($attribute !== 'href' || str_starts_with($url, '/assets/'))) {
                        $this->asset($url, $field);
                    }
                }
            }
        }
    }

    public function validate(array $datasets): array
    {
        $this->errors = [];
        $site = $datasets['site'];
        if ($this->expect($site instanceof stdClass, 'site.json', 'an object', $site)) {
            $this->fields($site, 'site.json', ['name', 'legal_name', 'tagline', 'phone', 'phone_href', 'email']);
            $this->url($site->url ?? null, 'site.json.url', false);
            $this->expect(is_string($site->email ?? null) && filter_var($site->email, FILTER_VALIDATE_EMAIL) !== false, 'site.json.email', 'an email address', $site->email ?? null);
            $this->expect(is_string($site->phone_href ?? null) && preg_match('/\A\+?[0-9-]+\z/', $site->phone_href), 'site.json.phone_href', 'a dialable telephone number', $site->phone_href ?? null);
            $this->strings($site->address ?? null, 'site.json.address', true);
            foreach (['hours', 'socials'] as $group) {
                $values = $site->$group ?? null;
                if ($this->expect($values instanceof stdClass, "site.json.$group", 'an object', $values)) {
                    foreach ($values as $key => $value) {
                        if ($group === 'socials') {
                            $this->url($value, "site.json.$group.$key", false);
                        } else {
                            $this->expect(is_string($value) && trim($value) !== '', "site.json.$group.$key", 'a non-blank schedule', $value);
                        }
                    }
                }
            }
            foreach ($this->records($site->nav ?? null, 'site.json.nav') as $path => $nav) {
                $this->fields($nav, $path, ['label']);
                $url = $nav->url ?? null;
                $this->expect(is_string($url) && preg_match('~^/(?:[a-z0-9-]+/)*$~D', $url), "$path.url", 'a local route with a trailing slash', $url);
            }
            $documents = $this->records($site->documents ?? null, 'site.json.documents');
            $this->slugs($this->records($site->documents ?? null, 'site.json.documents'));
            foreach ($documents as $path => $document) {
                $this->fields($document, $path, ['title', 'summary']);
            }
        }
        $services = $this->records($datasets['services'], 'services.json');
        foreach ($services as $path => $record) {
            $this->fields($record, $path, ['title', 'summary', 'category'], ['home_summary']);
            $this->lengths($record, $path, ['title', 'category'], ['summary', 'home_summary']);
            $this->strings($record->body ?? null, "$path.body", true);
            $this->strings($record->scope ?? null, "$path.scope", true);
        }
        $this->slugs($this->records($datasets['services'], 'services.json'));

        foreach ($this->records($datasets['team'], 'team.json') as $path => $person) {
            $this->fields($person, $path, ['name', 'role']);
            $this->lengths($person, $path, ['name', 'role', 'portrait']);
            if (property_exists($person, 'portrait')) {
                $this->image($person->portrait, "$path.portrait");
            }
        }

        $articles = $this->records($datasets['articles'], 'articles.json');
        foreach ($articles as $path => $article) {
            $this->fields($article, $path, ['title', 'body_html'], ['summary', 'category', 'author', 'image_alt']);
            $this->lengths($article, $path, ['title', 'category', 'author', 'image', 'image_alt'], ['summary']);
            if (property_exists($article, 'date')) {
                $date = $article->date;
                $this->expect(PublicationContent::date($date) !== null && $date >= '1000-01-01', "$path.date", 'a valid YYYY-MM-DD date from year 1000', $date);
            }
            if (property_exists($article, 'image')) {
                $this->image($article->image, "$path.image");
            }
            if (property_exists($article, 'status')) {
                $this->expect(in_array($article->status, ['published', 'draft', 'unpublished'], true), "$path.status", 'draft, published or unpublished', $article->status);
            }
            if (is_string($article->body_html ?? null) && trim($article->body_html) !== '') {
                $this->articleMarkup($article->body_html, "$path.body_html");
            }
        }
        $this->slugs($this->records($datasets['articles'], 'articles.json'), PublicUrl::RESERVED_SLUGS);

        $recruitment = $datasets['recruitment'];
        if ($this->expect($recruitment instanceof stdClass, 'recruitment.json', 'an object', $recruitment)) {
            foreach ($this->records($recruitment->vacancies ?? null, 'recruitment.json.vacancies') as $path => $vacancy) {
                $status = $vacancy->status ?? null;
                $this->expect(in_array($status, ['open', 'closed', 'draft'], true), "$path.status", 'open, closed or draft', $status);
                $this->fields($vacancy, $path, $status === 'open' ? ['title'] : [], ['title', 'description']);
                $this->lengths($vacancy, $path, ['title'], ['description', 'url']);
                if (property_exists($vacancy, 'url')) {
                    $this->url($vacancy->url, "$path.url", true, true);
                }
            }
        }
        return $this->errors;
    }
}
