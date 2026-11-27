<?php

namespace App\Filament\Pages;

use App\Analytics\PageViews;
use App\Analytics\TrafficReport;
use App\Enums\ContentSection;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use UnitEnum;

final class Statistics extends Page
{
    protected static ?string $slug = 'statystyki';

    protected static ?string $title = 'Statystyki';

    protected static string|UnitEnum|null $navigationGroup = 'Administracja';

    protected static ?int $navigationSort = 0;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.statistics';

    public string $period = '30';

    public string $customStart = '';

    public string $customEnd = '';

    #[Locked]
    public string $start = '';

    #[Locked]
    public string $end = '';

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->canAccessSection(ContentSection::Analytics);
    }

    public function mount(): void
    {
        $today = PageViews::today();
        $this->customStart = $this->start = $today->subDays(29)->toDateString();
        $this->customEnd = $this->end = $today->toDateString();
    }

    public function applyRange(): void
    {
        $today = PageViews::today();
        $this->validate(['period' => 'required|in:7,30,90,custom'], ['period.in' => 'Wybierz dostępny okres.']);
        if ($this->period !== 'custom') {
            $this->start = $today->subDays((int) $this->period - 1)->toDateString();
            $this->end = $today->toDateString();

            return;
        }

        $dates = Validator::make(['customStart' => $this->customStart, 'customEnd' => $this->customEnd], [
            'customStart' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01'],
            'customEnd' => ['required', 'date_format:Y-m-d', 'after_or_equal:customStart', 'before_or_equal:'.$today->toDateString()],
        ], [
            'customStart.*' => 'Wybierz poprawną datę początkową.',
            'customEnd.*' => 'Data końcowa musi przypadać między datą początkową a dzisiaj.',
        ])->validate();
        $start = CarbonImmutable::parse($dates['customStart'], PageViews::TIMEZONE);
        $end = CarbonImmutable::parse($dates['customEnd'], PageViews::TIMEZONE);
        if ($start->diffInDays($end) >= TrafficReport::MAX_RANGE_DAYS) {
            $this->addError('customEnd', 'Zakres może obejmować najwyżej 366 dni.');

            return;
        }

        $this->start = $dates['customStart'];
        $this->end = $dates['customEnd'];
    }

    protected function getViewData(): array
    {
        $start = CarbonImmutable::parse($this->start, PageViews::TIMEZONE);
        $end = CarbonImmutable::parse($this->end, PageViews::TIMEZONE);

        return TrafficReport::forRange($start, $end) + ['rangeStart' => $start, 'rangeEnd' => $end];
    }
}
