<a class="home-contact-float" href="{{ $path === '/kontakt/' ? '#formularz' : ($previewPublicOrigin ?? '') . '/kontakt/' }}"
   aria-label="{{ $path === '/kontakt/' ? 'Przejdź do formularza kontaktowego' : 'Kontakt z kancelarią' }}"
   data-control-tone="{{ $path === '/' ? 'burgundy' : 'light' }}" data-home-surface="{{ $path === '/' ? 'light' : 'beige' }}">
  <span class="home-contact-label">Kontakt</span>
  <span class="home-contact-face" aria-hidden="true" hidden><span class="home-contact-label">Kontakt</span></span>
</a>
