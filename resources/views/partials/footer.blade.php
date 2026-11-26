@if ($path === '/')
<section class="home-contact home-section" aria-labelledby="home-contact-title" data-home-surface="burgundy">
  <div class="home-frame home-contact-layout" data-reveal>
    <h2 id="home-contact-title">Każda sprawa zaczyna się od&nbsp;rozmowy.</h2>
    <div class="home-contact-detail">
      <p>Przedstaw nam swoją sytuację. Pomożemy ustalić możliwe kierunki dalszego działania.</p>
      <a class="button home-contact-action" href="/kontakt/" data-home-surface="light">Skontaktuj się z&nbsp;kancelarią</a>
    </div>
  </div>
</section>
@endif
<footer class="site-footer" data-home-surface="{{ $path === '/' ? 'burgundy' : 'beige' }}">
  @if ($path !== '/')
    <div class="container footer-callout" data-reveal>
      <h2>Porozmawiajmy o sprawie.</h2>
      <a class="text-action" href="/kontakt/">Skontaktuj się z kancelarią</a>
    </div>
  @endif
  <div class="container footer-grid" data-reveal-children>
    <div class="footer-identity">
      <a class="footer-brand" href="/">{{ $site['name'] }}</a>
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
          <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
        @endforeach
      </ul>
    </nav>
  </div>

  <div class="container legal-row" data-reveal>
    <p>© {{ date('Y') }} {{ $site['legal_name'] }}. Wszelkie prawa zastrzeżone.</p>
    <ul>
      <li><a href="/dokumenty/">Dokumenty</a></li>
      <li><a href="/dokumenty/polityka-prywatnosci/">Polityka prywatności</a></li>
    </ul>
  </div>
</footer>
