@extends('layouts.public')

@section('content')
<section class="publication-hero" aria-labelledby="publications-title">
  <div class="publication-frame publication-hero-layout">
    <div data-reveal>
      <span class="section-kicker">Artykuły</span>
      <h1 id="publications-title">Publikacje prawne.</h1>
    </div>
    <p data-reveal>Analizy i&nbsp;komentarze do zmian w&nbsp;prawie.</p>
  </div>
</section>

<section class="publication-archive{{ $publications ? '' : ' archive-empty' }}" aria-label="Publikacje" data-publication-archive>
  <div class="publication-frame">
    @if ($publications)
      <form class="publication-filters" role="search" aria-label="Szukaj i filtruj artykuły" data-publication-filters data-reveal hidden>
        <div class="publication-search">
          <label>
            @include('partials.search-icon')
            <span class="visually-hidden">Szukaj artykułów</span>
            <input type="search" name="query" placeholder="Szukaj artykułów" autocomplete="off" aria-controls="publication-list">
          </label>
          <button class="publication-search-clear" type="button" aria-label="Wyczyść wyszukiwanie" data-publication-clear hidden>
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false">
              <path d="m4 4 8 8m0-8-8 8"/>
            </svg>
          </button>
        </div>

        <label class="publication-filter">
          <span>Tematyka</span>
          <select name="category" aria-controls="publication-list">
            <option value="">Wszystkie kategorie</option>
            @foreach ($categories as $category)
              <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
          </select>
        </label>

        <label class="publication-filter">
          <span>Rok publikacji</span>
          <select name="year" aria-controls="publication-list">
            <option value="">Wszystkie lata</option>
            @foreach ($years as $year)
              <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
          </select>
        </label>

        <label class="publication-filter">
          <span>Sortowanie</span>
          <select name="sort" aria-controls="publication-list">
            <option value="newest">Od najnowszych</option>
            <option value="oldest">Od najstarszych</option>
            <option value="az">Alfabetycznie A–Z</option>
            <option value="za">Alfabetycznie Z–A</option>
          </select>
        </label>

        <div class="publication-filter-feedback">
          <p class="filter-status" data-publication-status role="status"></p>
          <button class="text-action publication-filter-reset" type="button" data-publication-reset hidden>Wyczyść filtry</button>
        </div>
      </form>
      <div id="publication-list" data-reveal-sequence>
        @foreach ($publications as $index => $publication)
          @php
$date = $publication['archive_date'];
          $featured = $index === 0 && $date !== '';
          $summary = is_string($publication['summary'] ?? null) ? trim($publication['summary']) : '';
          $bodyHtml = is_string($publication['body_html'] ?? null) ? $publication['body_html'] : '';
          $body = preg_replace('~</(?:p|li|h[1-6]|div|blockquote|td|th|figcaption)\s*>|<br\b[^>]*>~i', ' ', $bodyHtml);
          $bodyText = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
          $searchText = preg_replace('/\s+/u', ' ', implode(' ', [$publication['title'], $summary, $publication['archive_category'], $bodyText]));
          $slug = $publication['slug'] ?? null;
          $linked = is_string($slug) && preg_match('/\A[a-z0-9-]+\z/D', $slug);
          $tag = $linked ? 'a' : 'div';
          $titleId = 'publication-title-' . $index;
@endphp
          <{{ $tag }}
            class="publication-entry editorial-row"
            data-reveal
            data-publication-title="{{ $publication['title'] }}"
            data-publication-date="{{ $date }}"
            data-publication-category="{{ $publication['archive_category'] }}"
            data-publication-search="{{ $searchText }}"
            @if ($linked)href="/{{ $slug }}/" aria-labelledby="{{ $titleId }}"@endif
          >
            <div class="publication-heading">
              @if ($featured)
                <p class="section-kicker">Najnowsza publikacja</p>
              @endif
              @include('partials.publication-meta', ['publication' => $publication])
              <h2 class="row-title" id="{{ $titleId }}">{{ $publication['title'] }}</h2>
            </div>
            @if ($summary !== '')
              <p class="publication-summary row-summary">{{ $summary }}</p>
            @endif
            @if ($linked)
              <span class="service-row-arrow publication-row-arrow" aria-hidden="true">&rarr;</span>
            @endif
          </{{ $tag }}>
        @endforeach
      </div>
    @else
      <div class="archive-note" data-reveal>
        <h2>Archiwum w przygotowaniu</h2>
        <p>W sprawie konkretnego zagadnienia napisz lub zadzwoń.</p>
        <a class="text-action" href="/kontakt/">Skontaktuj się</a>
      </div>
    @endif
  </div>
</section>
@endsection
