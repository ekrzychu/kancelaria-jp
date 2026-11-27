@extends('layouts.public')

@section('content')
@php
// Set to false until the firm confirms permission to publish the logos.
$showClientLogos = true;
$clientLogos = [
    ['bank-millennium', 'Bank Millennium', 440, 94],
    ['nest-bank', 'Nest Bank', 440, 174],
    ['ergo-hestia', 'ERGO Hestia', 317, 176],
    ['gowhistle', 'GoWhistle', 440, 115],
    ['asps-budownictwo', 'ASPS Budownictwo', 440, 67],
    ['wsb-merito', 'Uniwersytety WSB Merito', 440, 93],
    ['ultimo', 'Ultimo', 440, 142],
    ['takto-finanse', 'Takto Finanse', 200, 68],
];
// Temporary value for this experiment. Verify the experience figure before production.
$experienceYears = 6;
@endphp

<section class="home-hero" aria-labelledby="home-title" data-home-stage="hero" data-home-surface="burgundy">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="burgundy"></div>
    <div class="home-stage-content">
      <div class="home-frame home-hero-layout">
        <div class="home-hero-heading" data-reveal>
          <p class="section-kicker">Kancelaria radców prawnych we Wrocławiu</p>
          <h1 id="home-title"><span>Prawo</span> <span class="home-hero-title-accent">w&nbsp;praktyce.</span></h1>
        </div>
        <div class="home-hero-intro" data-reveal>
          <div class="home-hero-copy">
            <p class="home-hero-lead">Doradzamy firmom, instytucjom i&nbsp;klientom&nbsp;indywidualnym.</p>
            <p>Reprezentujemy ich również w&nbsp;sporach&nbsp;sądowych.</p>
          </div>
          <a class="home-hero-action" href="#specjalizacje">
            <span>Zobacz specjalizacje</span>
            <svg viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
              <path d="M2 9h24m-7-7 7 7-7 7"/>
            </svg>
          </a>
        </div>
        <picture class="home-hero-photo" data-reveal="image" data-home-surface="photo">
          <img src="/assets/img/office-reception-hero.jpg"
            alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1150" height="1480" fetchpriority="high">
        </picture>
      </div>
    </div>
  </div>
</section>

<section class="home-practices home-section" id="specjalizacje" tabindex="-1" aria-labelledby="home-practices-title" data-home-stage="practices" data-home-surface="light">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="light"></div>
    <div class="home-stage-content">
      <div class="home-frame home-practices-layout">
        <div class="home-practices-heading">
          <div class="home-practices-intro" data-reveal>
            <p class="section-kicker">Specjalizacje</p>
            <h2 id="home-practices-title">W czym możemy pomóc?</h2>
            <a class="text-action" href="/specjalizacje/">Pełna lista specjalizacji</a>
          </div>
        </div>
        <div class="home-practices-rail" data-reveal>
          <ul class="home-practice-list" role="list">
            @foreach ($services as $service)
              @php
$summary = $service['home_summary'] ?? $service['summary'];
              $summary = preg_replace('/(?<!\p{L})([aiouwz]) (?=\p{L})/iu', '$1' . "\u{00A0}", $summary) ?? $summary;
@endphp
              <li>
                <a class="home-practice-link" href="/specjalizacje/{{ $service['slug'] }}/">
                  <span>{{ $service['title'] }}</span>
                  <small>{{ $summary }}</small>
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

@if ($publications)
  <section class="home-publications home-section" aria-labelledby="home-publications-title" data-home-stage="publications" data-home-surface="burgundy">
    <div class="home-stage-viewport">
      <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="burgundy"></div>
      <div class="home-stage-content">
        <div class="home-frame home-publications-layout">
          <div class="home-publications-heading" data-reveal>
            <h2 id="home-publications-title">Publikacje</h2>
            <a class="text-action" href="/artykuly/">Wszystkie publikacje</a>
          </div>
          <ol class="home-publications-list" role="list">
            @foreach ($publications as $publication)
              <li>
                <article data-reveal>
                  <time datetime="{{ $publication['published']->format('Y-m-d') }}">{{ $publication['published']->format('d.m.Y') }}</time>
                  <h3>
                    @if ($publication['slug'] !== '')
                      <a href="/{{ $publication['slug'] }}/">{{ $publication['title'] }}</a>
                    @else
                      {{ $publication['title'] }}
                    @endif
                  </h3>
                  <p>{{ $publication['summary'] }}</p>
                </article>
              </li>
            @endforeach
          </ol>
        </div>
      </div>
    </div>
  </section>
@endif

<section class="home-profile home-section" aria-labelledby="home-profile-title" data-home-stage="profile" data-home-surface="beige">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="beige"></div>
    <div class="home-stage-content">
      <div class="home-frame home-profile-layout" data-home-surface="beige">
        <div class="home-profile-statement" data-reveal>
          <p class="section-kicker">O kancelarii</p>
          <h2 id="home-profile-title">Najpierw sprawa.<br>Potem strategia.</h2>
        </div>
        <div class="home-profile-side">
          <div class="home-profile-detail" data-reveal>
            <p>Ustalamy, co jest dla klienta najważniejsze, i&nbsp;przedstawiamy możliwe działania oraz ich konsekwencje. Wspieramy w&nbsp;pojedynczych sprawach i&nbsp;w&nbsp;stałej współpracy.</p>
            <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię</a>
          </div>
          <p class="home-experience" data-reveal="experience">
            <span class="visually-hidden">{{ $experienceYears }}+</span>
            <span class="home-experience-value" aria-hidden="true">
              <span class="home-experience-number" data-final-value="{{ $experienceYears }}"><span>{{ $experienceYears }}</span></span><span class="home-experience-plus">+</span>
            </span>
            <span>lat doświadczenia</span>
          </p>
        </div>
        <div class="home-team" data-reveal>
          <div>
            <h3>Zespół kancelarii</h3>
            <a class="text-action" href="/zespol/">Poznaj cały zespół</a>
          </div>
          @if ($partners)
            <ul role="list">
              @foreach ($partners as $person)
                <li>
                  <p>{{ $person['name'] }}</p><span>{{ $person['role'] }}</span>
                  @if ($person['portrait_media_id'])
                    <img class="home-team-portrait" src="{{ \App\Models\MediaImage::url($person['portrait_media_id']) }}" alt="" loading="lazy" decoding="async">
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

<section class="home-office home-section" aria-labelledby="home-office-title" data-home-stage="office" data-home-surface="light">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true"></div>
    <div class="home-stage-content">
      <div class="home-frame home-office-layout">
        <picture class="home-office-photo" data-reveal="image" data-home-surface="photo">
          <img src="/assets/img/office-entrance-1280.webp"
            srcset="/assets/img/office-entrance-760.webp 760w, /assets/img/office-entrance-1280.webp 1280w, /assets/img/office-entrance-2240.webp 2240w"
            sizes="(max-width: 42rem) 90vw, (max-width: 56rem) 49vw, (min-width: 129rem) 66rem, 55vw"
            alt="Drewniane drzwi wejściowe i numer 7–8 na kamiennej fasadzie przy placu Kościuszki" width="1280" height="1990" loading="lazy" decoding="async">
        </picture>
        <div class="home-office-copy" data-reveal data-home-surface="beige">
          <p class="section-kicker">Siedziba kancelarii</p>
          <h2 id="home-office-title">Wrocław.</h2>
          <address>@include('partials.office-address')</address>
          <a class="text-action" href="/kontakt/">Dane kontaktowe i godziny pracy</a>
        </div>
      </div>
    </div>
  </div>
</section>

@if ($showClientLogos)
  <section class="home-clients" aria-labelledby="home-clients-title" data-home-stage="clients" data-home-surface="beige">
    <div class="home-stage-viewport">
      <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="beige"></div>
      <div class="home-stage-content">
        <div class="home-frame home-clients-layout">
          <h2 id="home-clients-title" data-reveal>Współpracujemy z</h2>
          <ul class="home-client-logos" data-reveal role="list">
            @foreach ($clientLogos as [$slug, $name, $width, $height])
              <li class="home-client-{{ $slug }}">
                <img src="/assets/img/clients/{{ $slug }}.webp" alt="{{ $name }}" width="{{ $width }}" height="{{ $height }}" loading="lazy" decoding="async">
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </section>
@endif
@endsection
