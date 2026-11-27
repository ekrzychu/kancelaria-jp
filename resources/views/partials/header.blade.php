<a class="skip-link" href="#main"@if ($path === '/') data-home-surface="light"@endif>Przejdź do treści</a>
<header class="site-header">
  <div class="header-bar"@if ($path === '/') data-home-surface="burgundy"@endif>
    <div class="container header-inner">
      <a class="brand" href="{{ $previewPublicOrigin ?? '' }}/" aria-label="{{ $site['name'] }} — strona główna"@if ($path === '/') aria-current="page"@endif>
        <span class="brand-mark" aria-hidden="true">
          @include('partials.brand-mark')
        </span>
        <span class="brand-text">
          <strong>{{ $site['name'] }}</strong>
          <small>{{ $site['tagline'] }}</small>
        </span>
      </a>
      <button class="menu-button" type="button" aria-controls="main-nav" aria-expanded="false" hidden>
        Menu <span class="menu-icon" aria-hidden="true"></span>
      </button>
      <div class="nav-panel">
        <nav class="main-nav" id="main-nav" aria-label="Menu główne">
          @foreach ($site['nav'] as $item)
            @php $current = $path === $item['url'] ? 'page' : (str_starts_with($path, rtrim($item['url'], '/') . '/') ? 'location' : null); @endphp
            <a class="nav-link" href="{{ ($previewPublicOrigin ?? '') . $item['url'] }}"@if ($current) aria-current="{{ $current }}"@endif>{{ $item['label'] }}</a>
          @endforeach
        </nav>
      </div>
    </div>
  </div>
</header>
