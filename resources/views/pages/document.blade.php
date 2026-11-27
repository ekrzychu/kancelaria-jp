@extends('layouts.public')

@section('content')
<article class="article-detail publication-frame document-detail" aria-labelledby="document-title">
  <header class="article-header" data-reveal>
    <div class="article-intro">
      <a class="section-kicker job-back" href="/dokumenty/">Dokumenty kancelarii</a>
      <h1 id="document-title">@yield('document-title', $document['title'])</h1>
      <p class="article-summary">{{ $document['summary'] }}</p>
      @yield('document-note')
    </div>
  </header>

  <div class="article-reading">
    <details class="article-contents" open>
      <summary>Spis treści</summary>
      <nav aria-label="Spis treści dokumentu">
        <ul>
          @foreach ($contents as $id => $heading)
            <li><a href="#{{ $id }}">{{ $heading }}</a></li>
          @endforeach
        </ul>
      </nav>
    </details>

    <div class="article-body" data-reveal-sequence>
      @yield('document-content')
      <a class="text-action" href="/dokumenty/">Wróć do dokumentów</a>
    </div>
  </div>
</article>
@endsection
