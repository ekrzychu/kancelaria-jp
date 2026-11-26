<?php

namespace App\Content;

use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\HtmlString;
use League\Uri\Uri;

final class PublicationContent
{
    public function home(array $articles): array
    {
        $publications = [];
        foreach ($articles as $article) {
            if (!is_array($article)) {
                continue;
            }

            $title = is_string($article['title'] ?? null) ? trim($article['title']) : '';
            $summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
            $published = self::date($article['date'] ?? null);
            if ($title !== '' && $summary !== '' && $published) {
                $publications[] = compact('title', 'summary', 'published') + ['slug' => $article['slug'] ?? ''];
            }
        }
        usort($publications, static fn ($left, $right) => $right['published'] <=> $left['published']);

        return array_slice($publications, 0, 3);
    }

    public function archive(array $articles): array
    {
        $publications = array_values(array_filter($articles, static fn ($entry) =>
            is_array($entry) && is_string($entry['title'] ?? null) && trim($entry['title']) !== ''
        ));
        foreach ($publications as &$publication) {
            $publication['archive_date'] = self::date($publication['date'] ?? null)?->format('Y-m-d') ?? '';
            $publication['archive_category'] = is_string($publication['category'] ?? null) ? trim($publication['category']) : '';
        }
        unset($publication);
        usort($publications, static fn (array $a, array $b): int => $b['archive_date'] <=> $a['archive_date']);

        $categories = array_unique(array_filter(array_column($publications, 'archive_category'), 'strlen'));
        $years = array_unique(array_filter(array_map(static fn (array $entry): string => substr($entry['archive_date'], 0, 4), $publications), 'strlen'));

        return compact('publications', 'categories', 'years');
    }

    public function related(array $articles, array $article): array
    {
        $related = [];
        foreach ($articles as $entry) {
            $slug = $entry['slug'] ?? null;
            if (!is_string($slug) || $slug === $article['slug'] || !preg_match('/\A[a-z0-9-]+\z/', $slug)
                || !is_string($entry['title'] ?? null) || trim($entry['title']) === '') {
                continue;
            }
            $entry['related_date'] = self::date($entry['date'] ?? null)?->format('Y-m-d') ?? '';
            $related[] = $entry;
        }
        usort($related, static fn (array $a, array $b): int =>
            $b['related_date'] <=> $a['related_date']
        );
        return array_slice($related, 0, 2);
    }

    public function detail(array $article, string $publicOrigin = ''): array
    {
        $summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
        $author = is_string($article['author'] ?? null) ? trim($article['author']) : '';
        $bodyHtml = app(ArticleHtml::class)->sanitize($article['body_html']);
        $sections = [];
        $sectionCount = 0;

        // Add navigation and wrappers after applying the same allowlist to every article.
        if (class_exists(DOMDocument::class)) {
            $document = new DOMDocument();
            $fragment = '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $bodyHtml . '</body></html>';
            if ($document->loadHTML($fragment, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                $body = $document->getElementsByTagName('body')->item(0);
                $xpath = new DOMXPath($document);
                if ($publicOrigin !== '') {
                    $publicArticle = Uri::new($publicOrigin.'/'.$article['slug'].'/');
                    foreach ($xpath->query('.//a[@href]', $body) as $link) {
                        $href = $link->getAttribute('href');
                        if ($href !== '' && ! str_starts_with($href, '#') && ! preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $href)) {
                            try {
                                $link->setAttribute('href', (string) $publicArticle->resolve($href));
                            } catch (\League\Uri\Exceptions\SyntaxError) {
                                $link->removeAttribute('href');
                            }
                        }
                    }
                }
                $ids = [];
                foreach ($xpath->query('.//*[@id]', $body) as $node) {
                    $id = $node->getAttribute('id');
                    $ids[$id] = ($ids[$id] ?? 0) + 1;
                }

                $polish = array_combine(
                    preg_split('//u', 'ąĄćĆęĘłŁńŃóÓśŚźŹżŻ', -1, PREG_SPLIT_NO_EMPTY),
                    str_split('aAcCeElLnNoOsSzZzZ')
                );
                foreach ($xpath->query('.//h2[not(ancestor::blockquote)] | .//h3[not(ancestor::blockquote)]', $body) as $heading) {
                    $label = trim(preg_replace('/\s+/u', ' ', $heading->textContent));
                    if ($label === '') {
                        continue;
                    }
                    $id = $heading->getAttribute('id');
                    if ($id === '' || $ids[$id] > 1) {
                        $slug = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', strtolower(strtr($label, $polish))), '-');
                        $base = 'sekcja-' . ($slug !== '' ? $slug : 'artykulu');
                        $id = $base;
                        for ($suffix = 2; isset($ids[$id]); $suffix++) {
                            $id = $base . '-' . $suffix;
                        }
                        $heading->setAttribute('id', $id);
                        $ids[$id] = 1;
                    }
                    $nested = $heading->tagName === 'h3';
                    $sectionCount += (int) !$nested;
                    $heading->setAttribute('tabindex', '-1');
                    $sections[] = ['id' => $id, 'label' => $label, 'nested' => $nested];
                }

                $tables = $xpath->query('.//table', $body);
                foreach ($tables as $table) {
                    $caption = trim($xpath->query('./caption', $table)->item(0)?->textContent ?? '');
                    $wrapper = $document->createElement('div');
                    $wrapper->setAttribute('class', 'article-table-scroll');
                    $wrapper->setAttribute('role', 'region');
                    $wrapper->setAttribute('aria-label', $caption !== '' ? $caption : 'Tabela w artykule');
                    $wrapper->setAttribute('tabindex', '0');
                    $table->parentNode->insertBefore($wrapper, $table);
                    $wrapper->appendChild($table);
                }

                if ($sections || $tables->length) {
                    $bodyHtml = '';
                    foreach ($body->childNodes as $node) {
                        $bodyHtml .= $document->saveHTML($node);
                    }
                }
            }
        }

        // Only reviewed, imported body_html is trusted here. DOM formatting is not sanitization.
        $body = new HtmlString($bodyHtml);

        return compact('body', 'sections', 'sectionCount', 'summary', 'author');
    }

    public static function date(mixed $date): ?DateTimeImmutable
    {
        if (! is_string($date) || ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date ? $parsed : null;
    }
}
