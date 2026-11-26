<?php

namespace App\Content;

use App\Models\Article;
use App\Models\JobOffer;
use App\Models\Specialization;
use App\Models\TeamMember;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ContentImporter
{
    public function prepare(array $source): array
    {
        $articles = array_map(static fn (object $article): array => Arr::only((array) $article, [
            'slug', 'title', 'summary', 'category', 'author', 'body_html', 'date', 'image', 'image_alt',
        ]) + ['status' => $article->status ?? ArticleStatus::Published->value], $source['articles']);

        $specializations = [];
        foreach ($source['services'] as $index => $service) {
            $specializations[] = Arr::only((array) $service, [
                'slug', 'title', 'category', 'summary', 'home_summary', 'body', 'scope',
            ]) + ['active' => true, 'display_order' => $index + 1];
        }

        $team = [];
        foreach ($source['team'] as $index => $person) {
            $team[] = Arr::only((array) $person, ['name', 'role', 'portrait']) + [
                'team_group' => TeamGroup::fromReviewedRole($person->role),
                'homepage_partner' => str_starts_with(mb_strtolower($person->role, 'UTF-8'), 'wspólnik'),
                'display_order' => $index + 1,
            ];
        }

        $jobs = [];
        foreach ($source['recruitment']->vacancies as $index => $job) {
            $jobs[] = Arr::only((array) $job, ['title', 'description', 'url', 'status']) + ['display_order' => $index + 1];
        }

        return [
            Article::class => $articles,
            Specialization::class => $specializations,
            TeamMember::class => $team,
            JobOffer::class => $jobs,
        ];
    }

    public function import(array $records): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mariadb') {
            throw new RuntimeException('The content importer requires the MariaDB connection.');
        }

        $lock = 'kjp-content-'.substr(hash('sha256', $connection->getDatabaseName()), 0, 40);
        $acquired = $connection->selectOne('SELECT GET_LOCK(?, 5) AS acquired', [$lock], false);
        if ((int) $acquired->acquired !== 1) {
            throw new RuntimeException('Another import is running. Try again after it finishes.');
        }

        try {
            $connection->transaction(static function () use ($records): void {
                foreach (array_keys($records) as $model) {
                    if ($model::withTrashed()->exists()) {
                        throw new RuntimeException('Content tables are already populated, including soft-deleted records. Import refused.');
                    }
                }

                foreach ($records as $model => $rows) {
                    foreach ($rows as $row) {
                        $model::create($row);
                    }
                }
            });
        } finally {
            $connection->selectOne('SELECT RELEASE_LOCK(?)', [$lock], false);
        }
    }
}
