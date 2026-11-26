@extends('layouts.public')

@section('content')
@php
$stages = [
    'Aplikacja' => 'Wyślij zgłoszenie zgodnie z wymaganiami danego naboru.',
    'Pierwsza rozmowa' => 'Wybrane osoby zapraszamy na spotkanie z koordynatorem zespołu, dyrektor zarządzającą lub z obojgiem.',
    'Dodatkowa rozmowa' => 'Po pierwszym spotkaniu możemy zaprosić Cię na rozmowę ze wspólnikami kancelarii.',
    'Decyzja' => 'Informację o wyniku rekrutacji przekazujemy mailowo lub telefonicznie.',
];
@endphp

<section class="career-hero" aria-labelledby="career-title">
  <div class="career-frame career-split">
    <div data-reveal="subtle">
      <span class="section-kicker">Kariera</span>
      <h1 id="career-title">Rozwijaj swoją karierę razem z&nbsp;nami.</h1>
    </div>
    <p class="career-intro" data-reveal="subtle">Jeśli chcesz rozwijać swój warsztat prawniczy i&nbsp;pracować nad sprawami wspólnie z&nbsp;innymi, dowiedz się, jak wygląda rekrutacja.</p>
  </div>
</section>

<section class="career-opportunities" aria-labelledby="career-opportunities-title">
  <div class="career-frame">
    <h2 id="career-opportunities-title" data-reveal="subtle">Aktualne oferty pracy</h2>
    @if ($vacancies)
      <ul class="editorial-list career-openings" role="list">
        @foreach ($vacancies as $vacancy)
          <li class="editorial-row career-opening" data-reveal="subtle">
            <h3>{{ $vacancy['title'] }}</h3>
            @if (!empty($vacancy['description']) || !empty($vacancy['url']))
              <div>
                @if (!empty($vacancy['description']))
                  <p>{{ $vacancy['description'] }}</p>
                @endif
                @if (!empty($vacancy['url']))
                  <a class="text-action career-action" href="{{ $vacancy['url'] }}" aria-label="{{ 'Szczegóły ogłoszenia — ' . $vacancy['title'] }}"><span>Szczegóły ogłoszenia</span>@include('partials.action-arrow', ['arrowClass' => 'career-arrow'])</a>
                @endif
              </div>
            @endif
          </li>
        @endforeach
      </ul>
    @else
      <div class="editorial-row career-opening" data-reveal="subtle">
        <h3>Brak opublikowanych ofert</h3>
        <div>
          <p>Jeśli interesuje Cię praca lub współpraca z&nbsp;kancelarią, możesz zapytać o&nbsp;możliwości zgłoszenia.</p>
          <a class="text-action career-action" href="#kontakt-rekrutacja"><span>Zapytaj o&nbsp;nabór</span>@include('partials.action-arrow', ['arrowClass' => 'career-arrow'])</a>
        </div>
      </div>
    @endif
  </div>
</section>

<section class="career-process" aria-labelledby="career-process-title" data-home-surface="beige">
  <div class="career-frame career-process-layout">
    <h2 id="career-process-title" data-reveal="subtle">Jak wygląda rekrutacja</h2>
    <ol class="career-steps" role="list" data-career-process>
      @php $number = 0; @endphp
      @foreach ($stages as $stepTitle => $stepDescription)
        <li class="career-step">
          <span class="career-step-marker" aria-hidden="true"></span>
          <div class="career-step-copy">
            <span class="career-step-index" aria-hidden="true">{{ sprintf('%02d', ++$number) }}</span>
            <h3>{{ $stepTitle }}</h3>
            <p>{{ $stepDescription }}</p>
          </div>
        </li>
      @endforeach
    </ol>
  </div>
</section>

<section class="career-application" id="kontakt-rekrutacja" aria-labelledby="career-application-title" tabindex="-1">
  <div class="career-frame career-split">
    <div data-reveal="subtle">
      <span class="section-kicker">Kontakt w sprawie rekrutacji</span>
      <h2 id="career-application-title">Chcesz do nas dołączyć?</h2>
    </div>
    <div class="career-application-copy" data-reveal="subtle">
      <p>Przed wysłaniem zgłoszenia zapytaj o&nbsp;aktualny adres rekrutacyjny, wymagane dokumenty oraz informacje o&nbsp;przetwarzaniu danych kandydatów.</p>
      <a class="text-action career-action" href="mailto:{{ $site['email'] }}?subject=Rekrutacja"><span>{{ $site['email'] }}</span>@include('partials.action-arrow', ['arrowClass' => 'career-arrow'])</a>
    </div>
  </div>
</section>
@endsection
