@extends('layouts.public')

@section('content')
<section class="page-hero services-hero">
  <div class="services-frame page-intro" data-reveal="subtle">
    <span class="section-kicker">Specjalizacje</span>
    <h1>Zakres wsparcia prawnego.</h1>
  </div>
</section>

<section class="services-directory" aria-label="Katalog specjalizacji">
  <div class="services-frame">
    <div class="filter-line" data-service-filters data-reveal="subtle" hidden>
      <div class="service-search">
        <label>
          @include('partials.search-icon')
          <span class="visually-hidden">Szukaj specjalizacji</span>
          <input type="search" placeholder="Szukaj specjalizacji" autocomplete="off" data-service-search aria-controls="service-list">
        </label>
        <button class="service-search-clear" type="button" aria-label="Wyczyść wyszukiwanie" data-service-clear hidden>
          <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="m4 4 8 8m0-8-8 8"/>
          </svg>
        </button>
      </div>
      <p class="filter-status" data-service-status aria-live="polite"></p>
    </div>

    <div class="editorial-list" id="service-list" data-service-list>
      @foreach ($services as $service)
        @php $searchText = mb_strtolower($service['title'] . ' ' . $service['summary'] . ' ' . $service['category']); @endphp
        <a
          class="editorial-row"
          data-service-row
          data-reveal="service-row"
          data-title="{{ $searchText }}"
          href="/specjalizacje/{{ $service['slug'] }}/"
        >
          <span class="row-heading">
            <span class="row-meta">{{ $service['category'] }}</span>
            <span class="row-title">{{ $service['title'] }}</span>
          </span>
          <span class="row-summary">{{ $service['summary'] }}</span>
          <span class="service-row-arrow" aria-hidden="true">&rarr;</span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
