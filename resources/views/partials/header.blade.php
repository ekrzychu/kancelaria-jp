<a class="skip-link" href="#main"@if ($path === '/') data-home-surface="light"@endif>Przejdź do treści</a>
<header class="site-header">
  <div class="header-bar"@if ($path === '/') data-home-surface="burgundy"@endif>
    <div class="container header-inner">
      <a class="brand" href="{{ $previewPublicOrigin ?? '' }}/" aria-label="{{ $site['name'] }} — strona główna"@if ($path === '/') aria-current="page"@endif data-reveal>
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 76 46" role="img" focusable="false" aria-hidden="true">
            <path d="M 23 7.2 V 33 c 0 1 0 2 -0.2 3 c -0.5 4 -2.2 7.4 -5 9.9 c 0.9 -0.2 1.8 -0.6 2.7 -1 c 5.7 -2.5 8.8 -6.9 9.6 -13 l 0.1 -3 V 0 L 23 7.2 Z M 68.7 0 v 23 a 12.8 12.8 0 0 0 0 -23 Z M 45.9 7.2 v 38.6 l 7.2 -7.2 V 0 l -7.2 7.2 Z M 7.3 46 V 23 a 12.9 12.9 0 0 0 0 23 Z M 76 38.7 V 46 h -7.3 l 7.3 -7.3 Z M 0 7.3 V 0 h 7.3 L 0 7.3 Z"></path>
          </svg>
        </span>
        <span class="brand-text">
          <strong>{{ $site['name'] }}</strong>
          <small>{{ $site['tagline'] }}</small>
        </span>
      </a>
      <button class="menu-button" type="button" aria-controls="main-nav" aria-expanded="false" data-reveal hidden>
        Menu <span class="menu-icon" aria-hidden="true"></span>
      </button>
      <div class="nav-panel">
        <nav class="main-nav" id="main-nav" aria-label="Menu główne">
          @foreach ($site['nav'] as $item)
            @php $current = $path === $item['url'] ? 'page' : (str_starts_with($path, rtrim($item['url'], '/') . '/') ? 'location' : null); @endphp
            <a class="nav-link" href="{{ ($previewPublicOrigin ?? '') . $item['url'] }}"@if ($current) aria-current="{{ $current }}"@endif data-reveal>{{ $item['label'] }}</a>
          @endforeach
        </nav>
      </div>
    </div>
  </div>
</header>
