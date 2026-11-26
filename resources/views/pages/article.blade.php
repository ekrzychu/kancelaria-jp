@extends('layouts.public')

@section('content')
<article class="article-detail publication-frame" aria-labelledby="article-title">
  <div class="article-progress" aria-hidden="true" hidden></div>
  <header class="article-header" data-reveal="subtle">
    <div class="article-intro">
      @include('partials.archive-link')
      <div class="article-meta">
        @include('partials.publication-meta', ['publication' => $article])
        @if ($author !== '')
          <p class="publication-meta">{{ $author }}</p>
        @endif
      </div>
      <h1 id="article-title">{{ $article['title'] }}</h1>
      @if ($summary !== '')
        <p class="article-summary">{{ $summary }}</p>
      @endif
    </div>
  </header>
  <div class="article-reading">
    @if ($sectionCount >= 2)
      <details class="article-contents" data-reveal="subtle">
        <summary>Spis treści</summary>
        <nav aria-label="Spis treści">
          <ul>
            @foreach ($sections as $section)
              <li @if ($section['nested'])class="article-contents-subsection"@endif>
                <a href="#{{ rawurlencode($section['id']) }}">{{ $section['label'] }}</a>
              </li>
            @endforeach
          </ul>
        </nav>
      </details>
    @endif
    <div class="article-body">
      {{ $body }}
    </div>
  </div>
  @if ($related)
    <section class="article-related" aria-labelledby="article-related-title">
      <header class="article-related-heading" data-reveal="subtle">
        <h2 id="article-related-title">Pozostałe publikacje</h2>
        @include('partials.archive-link')
      </header>
      <div data-reveal-sequence>
        @foreach ($related as $index => $publication)
          @php $relatedSummary = is_string($publication['summary'] ?? null) ? trim($publication['summary']) : ''; @endphp
          <a class="publication-entry editorial-row" data-reveal="publication-row"
             href="/{{ $publication['slug'] }}/" aria-labelledby="article-related-{{ $index }}">
            <div class="publication-heading">
              @include('partials.publication-meta', ['publication' => $publication])
              <h3 class="row-title" id="article-related-{{ $index }}">{{ $publication['title'] }}</h3>
            </div>
            @if ($relatedSummary !== '')
              <p class="publication-summary row-summary">{{ $relatedSummary }}</p>
            @endif
            <span class="service-row-arrow publication-row-arrow" aria-hidden="true">&rarr;</span>
          </a>
        @endforeach
      </div>
    </section>
  @else
    <nav class="article-navigation" aria-label="Nawigacja po artykułach">
      @include('partials.archive-link')
    </nav>
  @endif
</article>
@endsection
