<?php

namespace App\Analytics;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Specialization;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

final class TrafficReport
{
    public const int MAX_RANGE_DAYS = 366;

    private const array RANKINGS = [
        'pages' => 'Najczęściej odwiedzane strony',
        'articles' => 'Najpopularniejsze artykuły',
        'services' => 'Najpopularniejsze specjalizacje',
        'sources' => 'Zewnętrzne źródła ruchu',
    ];

    public static function forRange(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $length = (int) $start->diffInDays($end) + 1;
        $previousStart = $start->subDays($length);
        $previousEnd = $start->subDay();

        $counts = PageViews::counters()->whereBetween('day', [$previousStart->toDateString(), $end->toDateString()])
            ->selectRaw('day, SUM(views) AS total')->groupBy('day')->pluck('total', 'day');

        $summarize = static function (CarbonImmutable $from, CarbonImmutable $to) use ($counts): array {
            $series = [];
            foreach (CarbonPeriod::create($from, '1 day', $to) as $day) {
                $date = $day->toDateString();
                $series[$date] = $counts->has($date) ? (int) $counts[$date] : null;
            }

            $available = count(array_filter($series, static fn (?int $count): bool => $count !== null));
            $total = $available === 0 ? null : array_sum($series);

            return [
                'series' => $series,
                'available' => $available,
                'views' => $total,
                'average' => $available === 0 ? null : $total / $available,
                'contacts' => ContactMessage::query()
                    ->where('created_at', '>=', $from->utc())
                    ->where('created_at', '<', $to->addDay()->utc())->count(),
            ];
        };

        $current = $summarize($start, $end);
        $previous = $summarize($previousStart, $previousEnd);

        return [
            'current' => $current,
            'previous' => $previous,
            'length' => $length,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'rankingTitles' => self::RANKINGS,
            'rankings' => $current['views'] > 0 ? self::rankings($start, $end) : [],
        ];
    }

    private static function rankings(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $base = PageViews::counters()->whereBetween('day', [$start->toDateString(), $end->toDateString()])
            ->where('page', '!=', '');
        $pages = static fn () => (clone $base)->selectRaw('page, SUM(views) AS total')
            ->groupBy('page')->orderByDesc('total')->orderBy('page')->limit(8);
        $top = [
            'pages' => $pages()->pluck('total', 'page'),
            'articles' => $pages()->where('page', 'like', 'article:%')->pluck('total', 'page'),
            'services' => $pages()->where('page', 'like', 'service:%')->pluck('total', 'page'),
        ];

        $keys = $top['pages']->keys()->merge($top['articles']->keys())
            ->merge($top['services']->keys())->unique();
        $slugs = static fn (string $type) => $keys->filter(fn (string $key): bool => str_starts_with($key, $type.':'))
            ->map(fn (string $key): string => Str::after($key, ':'))->values()->all();
        $articles = Article::published()->whereIn('slug', $slugs('article'))->pluck('title', 'slug');
        $services = Specialization::visible()->whereIn('slug', $slugs('service'))->pluck('title', 'slug');
        $label = static function (string $key) use ($articles, $services): string {
            [$type, $slug] = array_pad(explode(':', $key, 2), 2, '');
            return match ($type) {
                'article' => $articles[$slug] ?? '/'.$slug.'/',
                'service' => $services[$slug] ?? '/specjalizacje/'.$slug.'/',
                'document' => '/dokumenty/'.$slug.'/',
                default => PageViews::PAGES[$key],
            };
        };

        $rankings = [];
        foreach ($top as $type => $rows) {
            $rankings[$type] = $rows->map(fn ($total, $key): array => [$label($key), (int) $total])->values()->all();
        }
        $rankings['sources'] = $base->whereIn('source', array_keys(PageViews::EXTERNAL_SOURCES))
            ->selectRaw('source, SUM(views) AS total')->groupBy('source')->orderByDesc('total')->orderBy('source')
            ->pluck('total', 'source')
            ->map(fn ($total, $source): array => [PageViews::EXTERNAL_SOURCES[$source], (int) $total])->values()->all();

        return $rankings;
    }
}
