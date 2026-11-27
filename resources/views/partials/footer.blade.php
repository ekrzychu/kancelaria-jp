<footer class="site-footer{{ $path === '/' ? ' site-footer-dark' : '' }}" data-home-surface="{{ $path === '/' ? 'burgundy' : 'beige' }}">
  <div class="container footer-callout" data-reveal>
    <h2>Porozmawiajmy o sprawie.</h2>
    <a class="text-action" href="{{ $previewPublicOrigin ?? '' }}/kontakt/">Skontaktuj się z kancelarią</a>
  </div>
  <div class="container footer-grid" data-reveal-children>
    <div class="footer-identity">
      <a class="footer-brand" href="{{ $previewPublicOrigin ?? '' }}/">{{ $site['name'] }}</a>
      <p>{{ $site['tagline'] }}</p>
      <ul class="social-links" aria-label="Portale społecznościowe">
        @foreach ($site['socials'] as $label => $href)
          <li><a href="{{ $href }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a></li>
        @endforeach
      </ul>
    </div>

    <div class="footer-contact">
      <address>
        <p>@include('partials.office-address')</p>
        <a href="tel:{{ $site['phone_href'] }}">{{ $site['phone'] }}</a><br>
        <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>
      </address>
    </div>

    <nav class="footer-nav" aria-label="Menu w stopce">
      <ul>
        @foreach ($site['nav'] as $item)
          <li><a href="{{ ($previewPublicOrigin ?? '') . $item['url'] }}">{{ $item['label'] }}</a></li>
        @endforeach
      </ul>
    </nav>
  </div>

  <div class="container legal-row" data-reveal>
    <p>© {{ date('Y') }} {{ $site['legal_name'] }}. Wszelkie prawa zastrzeżone.</p>
    <ul>
      <li><a href="{{ $previewPublicOrigin ?? '' }}/dokumenty/">Dokumenty</a></li>
      <li><a href="{{ $previewPublicOrigin ?? '' }}/dokumenty/polityka-prywatnosci/">Polityka prywatności</a></li>
    </ul>
  </div>
</footer>
