<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1>Kancelaria tworzona przez praktyków.</h1>
      <p>Obsługa prawna oparta na wiedzy, doświadczeniu i odpowiedzialnym prowadzeniu spraw.</p>
    </div>
    <picture class="page-hero-photo" data-reveal="image">
      <source type="image/webp" srcset="/assets/img/building-exterior-portrait-760.webp 760w, /assets/img/building-exterior-portrait.webp 1000w" sizes="(max-width: 42rem) 91vw, (max-width: 92rem) 39vw, 35rem">
      <img src="/assets/img/building-exterior-portrait.jpg" alt="Detal fasady siedziby kancelarii we Wrocławiu" width="1000" height="1300">
    </picture>
  </div>
</section>

<section class="section">
  <div class="container editorial-split">
    <div data-reveal>
      <h2>Kim jesteśmy</h2>
    </div>
    <div class="rich-text">
      <p>Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. wspiera klientów biznesowych i indywidualnych w sprawach wymagających precyzyjnego doradztwa, reprezentacji i dobrej organizacji procesu.</p>
      <p>Zespół kancelarii tworzą radcowie prawni, adwokaci, specjaliści prawni i administracyjni. Dzięki temu możliwe jest prowadzenie wielu spraw równolegle, bez utraty kontroli nad szczegółem.</p>
    </div>
  </div>
</section>

<section class="section about-method">
  <div class="container editorial-split">
    <div data-reveal>
      <h2>Jak pracujemy</h2>
    </div>
    <dl class="method-list">
      <div>
        <dt>Rozpoznanie sprawy</dt>
        <dd>Ustalamy fakty, dokumenty i interes klienta.</dd>
      </div>
      <div>
        <dt>Dobór rozwiązania</dt>
        <dd>Przedstawiamy rekomendację i możliwe warianty działania.</dd>
      </div>
      <div>
        <dt>Prowadzenie</dt>
        <dd>Realizujemy przyjętą strategię i dbamy o komunikację.</dd>
      </div>
    </dl>
  </div>
</section>

<section class="section">
  <div class="container editorial-split">
    <div data-reveal>
      <span class="section-kicker">Dokumenty</span>
      <h2>Standardy i informacje formalne.</h2>
    </div>
    <div class="editorial-list">
      <?php foreach ($site['documents'] as $doc): ?>
        <a class="editorial-row" href="/dokumenty/<?= e($doc['slug']) ?>/">
          <span class="row-title"><?= e($doc['title']) ?></span>
          <span class="row-summary"><?= e($doc['summary']) ?></span>
          <span class="row-indicator" aria-hidden="true"><?= icon('arrow', 'inline-arrow') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
