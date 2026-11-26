@extends('layouts.public')

@section('content')
<section class="service-hero" aria-labelledby="service-title">
  <div class="services-frame service-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">{{ $service['category'] }}</span>
      <h1 id="service-title">{{ $service['title'] }}</h1>
    </div>
    <p data-reveal="subtle">{{ $service['summary'] }}</p>
  </div>
</section>

<section class="service-details" aria-label="Informacje o specjalizacji">
  <div class="services-frame service-layout">
    <article class="service-content">
      <div class="service-description" data-reveal="subtle">
        @foreach ($service['body'] as $paragraph)
          <p>{{ $paragraph }}</p>
        @endforeach
      </div>

      <section class="service-scope-section" aria-labelledby="service-scope-title">
        <h2 id="service-scope-title" data-reveal="subtle">Zakres działań</h2>
        <ul class="service-scope" role="list">
          @foreach ($service['scope'] as $point)
            <li data-reveal="subtle">{{ $point }}</li>
          @endforeach
        </ul>
      </section>

      <section class="service-contact" aria-labelledby="service-contact-title" data-reveal="subtle">
        <h2 id="service-contact-title">Porozmawiajmy o&nbsp;Twojej sprawie.</h2>
        <div>
          <p>Skontaktuj się z&nbsp;kancelarią, aby omówić możliwy zakres współpracy.</p>
          <a class="text-action service-action" href="/kontakt/">
            <span>Skontaktuj się</span>
            <svg class="service-action-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 9h24m-7-7 7 7-7 7"/></svg>
          </a>
        </div>
      </section>
    </article>

    <nav class="side-index" aria-label="Specjalizacje" data-reveal="subtle">
      <a class="side-index-heading" href="/specjalizacje/">Wszystkie specjalizacje</a>
      @foreach ($services as $item)
        <a href="/specjalizacje/{{ $item['slug'] }}/"@if ($item['slug'] === $service['slug']) aria-current="page"@endif>
          {{ $item['title'] }}
        </a>
      @endforeach
    </nav>
  </div>
</section>
@endsection
