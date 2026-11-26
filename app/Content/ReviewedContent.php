<?php

namespace App\Content;

use JsonException;
use RuntimeException;

final readonly class ReviewedContent
{
    public const DATASETS = ['site', 'services', 'team', 'articles', 'recruitment'];

    public function __construct(private string $directory) {}

    public function read(string $dataset, bool $associative = false): mixed
    {
        if (! in_array($dataset, self::DATASETS, true)) {
            throw new RuntimeException("Unknown content dataset: {$dataset}.");
        }

        $path = $this->directory."/{$dataset}.json";
        $json = is_file($path) ? file_get_contents($path) : false;
        if ($json === false) {
            throw new RuntimeException("Unable to read content dataset: {$dataset}.json.");
        }

        return self::decode($dataset, $json, $associative);
    }

    public function all(): array
    {
        $datasets = [];
        foreach (self::DATASETS as $name) {
            $datasets[$name] = $this->read($name);
        }

        return $datasets;
    }

    public static function decode(string $dataset, string $json, bool $associative = false): mixed
    {
        try {
            return json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("{$dataset}.json: invalid JSON ({$exception->getMessage()})", 0, $exception);
        }
    }
}
