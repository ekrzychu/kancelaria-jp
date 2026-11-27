@extends('layouts.public')

@section('content')
<section class="team-hero" aria-labelledby="team-title">
  <div class="team-frame team-hero-layout">
    <div data-reveal>
      <span class="section-kicker">Zespół</span>
      <h1 id="team-title">Ludzie stojący za sprawami.</h1>
    </div>
    <p data-reveal>Nasz zespół tworzą radcowie prawni, adwokaci i&nbsp;specjaliści wspierający codzienną pracę kancelarii. Łączymy doświadczenie i&nbsp;różne perspektywy w&nbsp;pracy nad sprawami klientów.</p>
  </div>
</section>

<div class="team-directory">
  @foreach ($teamGroups as $group => ['heading' => $heading, 'people' => $people])
    @continue (! $people)
    @php $headingId = 'team-' . $group . '-title'; @endphp
    <section class="team-group team-group--{{ $group }}" aria-labelledby="{{ $headingId }}"@if ($group === 'leadership') data-home-surface="beige"@endif>
      <div class="team-frame team-group-layout">
        <h2 class="team-group-heading" id="{{ $headingId }}" data-reveal>{{ $heading }}</h2>
        <ul class="team-list" role="list" data-reveal-sequence>
          @foreach ($people as $person)
            <li class="team-person{{ $person['portrait_url'] ? ' has-portrait' : '' }}" data-reveal>
              @if ($person['portrait_url'])
                <div class="team-portrait-frame">
                  <img class="team-portrait" src="{{ $person['portrait_url'] }}" alt="" loading="lazy" decoding="async">
                </div>
              @endif
              <div>
                <h3>{{ $person['name'] }}</h3>
                <p>{{ $person['role'] }}</p>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    </section>
  @endforeach
</div>

<section class="team-career" aria-labelledby="team-career-title">
  <div class="team-frame team-career-layout">
    <h2 id="team-career-title" data-reveal>Dołącz do naszego zespołu.</h2>
    <div data-reveal>
      <p>Sprawdź informacje o&nbsp;pracy i&nbsp;możliwościach współpracy z&nbsp;naszą kancelarią.</p>
      <a class="text-action team-career-action" href="/kariera/">
        <span>Zobacz karierę</span>
        <svg class="team-career-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
          <path d="M2 9h24m-7-7 7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>
@endsection
