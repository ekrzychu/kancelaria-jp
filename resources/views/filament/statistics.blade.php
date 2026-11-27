<x-filament-panels::page>
    <div class="panel-statistics">
        <form class="panel-statistics-filters" wire:submit="applyRange" x-data="{ custom: $wire.period === 'custom' }">
            <label>
                <span>Okres</span>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="period" x-on:change="custom = $event.target.value === 'custom'">
                        <option value="7">Ostatnie 7 dni</option>
                        <option value="30">Ostatnie 30 dni</option>
                        <option value="90">Ostatnie 90 dni</option>
                        <option value="custom">Zakres własny</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
            <div class="panel-statistics-dates" x-show="custom" x-cloak>
                <label>
                    <span>Od</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model="customStart" />
                    </x-filament::input.wrapper>
                </label>
                <label>
                    <span>Do</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model="customEnd" />
                    </x-filament::input.wrapper>
                </label>
            </div>
            <x-filament::button type="submit">Pokaż</x-filament::button>
        </form>
        @foreach (['period', 'customStart', 'customEnd'] as $field)
            @error($field)
                <p class="panel-statistics-error" role="alert">{{ $message }}</p>
            @enderror
        @endforeach

        <div aria-live="polite">
            <p class="panel-statistics-period">
                <strong>{{ $rangeStart->format('d.m.Y') }} – {{ $rangeEnd->format('d.m.Y') }}</strong>
                <span>Poprzedni okres: {{ $previousStart->format('d.m.Y') }} – {{ $previousEnd->format('d.m.Y') }}</span>
            </p>
            @unless (config('analytics.enabled'))
                <p class="panel-statistics-note">Zliczanie odsłon jest wyłączone. Nadal można przeglądać zapisane odsłony i liczbę zgłoszeń.</p>
            @endunless
            @if ($current['available'] === 0)
                <p class="panel-statistics-note" data-coverage="missing">Brak danych o odsłonach w tym okresie.</p>
            @elseif ($current['available'] < $length)
                <p class="panel-statistics-note" data-coverage="partial">Dane o odsłonach obejmują {{ $current['available'] }} z {{ $length }} dni. Średnia uwzględnia tylko dni z danymi.</p>
            @endif

            <dl class="panel-statistics-totals">
                @foreach (['views' => 'Odsłony', 'contacts' => 'Zgłoszenia z formularza', 'average' => 'Średnia dzienna odsłon'] as $key => $label)
                    @php
                        $comparable = $key === 'contacts' || ($current['available'] === $length && $previous['available'] === $length);
                        $decimals = $key === 'average' ? 1 : 0;
                        $delta = $comparable ? round($current[$key] - $previous[$key], $decimals) : null;
                    @endphp
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd data-metric="{{ $key }}">{{ $current[$key] === null ? '—' : number_format($current[$key], $decimals, ',', ' ') }}</dd>
                        <dd class="panel-statistics-comparison">
                            @if ($delta === null)
                                Brak pełnych danych do porównania
                            @else
                                {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, $decimals, ',', ' ') }} względem poprzedniego okresu
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @if ($current['available'] > 0)
            @livewire(\App\Filament\Widgets\DailyTraffic::class, ['series' => $current['series']], key(md5(json_encode($current['series']))))

            <div class="panel-statistics-rankings">
                @foreach ($rankingTitles as $key => $heading)
                    <section>
                        <h2>{{ $heading }}</h2>
                        @if (!empty($rankings[$key]))
                            <table aria-label="{{ $heading }}">
                                <thead>
                                    <tr><th scope="col">{{ $key === 'sources' ? 'Źródło' : 'Strona' }}</th><th scope="col">Odsłony</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($rankings[$key] as [$label, $total])
                                        <tr><th scope="row">{{ $label }}</th><td>{{ number_format($total, 0, ',', ' ') }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Brak odnotowanych odsłon.</p>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
