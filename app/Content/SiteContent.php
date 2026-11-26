<?php

namespace App\Content;

use App\Support\PublicUrl;
use RuntimeException;

final class SiteContent
{
    private const DATASETS = ['site', 'services', 'team', 'articles', 'recruitment'];

    public function read(string $name): array
    {
        if (! in_array($name, self::DATASETS, true)) {
            throw new RuntimeException("Unknown content dataset: {$name}.");
        }

        $path = config('site.content_path')."/{$name}.json";
        $json = is_file($path) ? file_get_contents($path) : false;
        if ($json === false) {
            throw new RuntimeException("Unable to read content dataset: {$name}.json.");
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            throw new RuntimeException("Invalid content dataset: {$name}.json.");
        }

        return $data;
    }

    public function publishedArticles(): array
    {
        return array_values(array_filter($this->read('articles'), static function (mixed $article): bool {
            return is_array($article)
                && (! array_key_exists('status', $article) || $article['status'] === 'published')
                && ! in_array($article['slug'] ?? null, PublicUrl::RESERVED_SLUGS, true);
        }));
    }

    public static function findBySlug(array $items, string $slug): ?array
    {
        foreach ($items as $item) {
            if (($item['slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }
}
