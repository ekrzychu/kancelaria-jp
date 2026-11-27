<?php

namespace App\Analytics;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class PageViews
{
    public const string TIMEZONE = 'Europe/Warsaw';

    public const array PAGES = [
        'home' => 'Strona główna', 'about' => 'O kancelarii', 'services.index' => 'Specjalizacje',
        'team' => 'Zespół', 'career' => 'Kariera', 'articles.index' => 'Artykuły',
        'contact' => 'Kontakt', 'documents.index' => 'Dokumenty',
    ];

    public const array EXTERNAL_SOURCES = [
        'search' => 'Wyszukiwarki', 'social' => 'Media społecznościowe', 'external' => 'Inne witryny',
    ];

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::today(self::TIMEZONE);
    }

    public static function counters(): Builder
    {
        return DB::table('daily_page_views');
    }

    public static function increment(string $day, string $page, string $source): void
    {
        DB::statement(
            'SET STATEMENT innodb_lock_wait_timeout=1 FOR INSERT INTO daily_page_views (day, page, source, views)
             VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE views = views + 1',
            [$day, $page, $source],
        );
    }

    public static function maintain(): void
    {
        $today = self::today();
        if (config('analytics.enabled')) {
            // A zero-valued marker records that collection was enabled on a day without traffic.
            self::counters()->insertOrIgnore(['day' => $today->toDateString(), 'page' => '', 'source' => '', 'views' => 0]);
        }

        self::counters()->where('day', '<', $today->subDays(config('analytics.retention_days') - 1)->toDateString())->delete();
    }
}
