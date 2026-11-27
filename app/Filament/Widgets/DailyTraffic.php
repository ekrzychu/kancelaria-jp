<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Statistics;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Locked;

final class DailyTraffic extends ChartWidget
{
    protected ?string $heading = 'Odsłony według dnia';

    protected ?string $pollingInterval = null;

    protected static ?bool $isLazyLoaded = false;

    protected ?string $maxHeight = '16rem';

    protected ?array $options = [
        'animation' => ['duration' => 0],
        'plugins' => ['legend' => ['display' => false]],
        'scales' => [
            'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8, 'maxRotation' => 0]],
            'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
        ],
    ];

    #[Locked]
    public array $series = [];

    public static function canView(): bool
    {
        return Statistics::canAccess();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        return [
            'labels' => array_map(static fn (string $day): string => substr($day, 8).'.'.substr($day, 5, 2), array_keys($this->series)),
            'datasets' => [[
                'label' => 'Odsłony',
                'data' => array_values($this->series),
                'backgroundColor' => '#843f5a',
                'maxBarThickness' => 24,
            ]],
        ];
    }

    public function getChartAssistiveContent(): string
    {
        $days = [];
        foreach ($this->series as $day => $count) {
            $days[] = $day.': '.($count ?? 'brak danych');
        }

        return implode('; ', $days);
    }
}
