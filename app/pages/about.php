<section class="page-hero">
  <div class="container page-hero-grid">
    <div data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1>Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <picture class="page-hero-photo" data-reveal="image">
      <source type="image/webp" srcset="/assets/img/building-exterior-portrait-760.webp 760w, /assets/img/building-exterior-portrait.webp 1000w" sizes="(max-width: 42rem) 91vw, (min-width: 160rem) 48rem, (min-width: 90rem) calc(19vw + 17rem), 39vw">
      <img src="/assets/img/building-exterior-portrait.jpg" alt="Detal fasady siedziby kancelarii we Wrocławiu" width="1000" height="1300">
    </picture>
  </div>
</section>

<section class="section">
  <div class="container editorial-split">
    <div data-reveal>
      <h2>Kim jesteśmy</h2>
    </div>
    <div class="rich-text" data-reveal>
      <p>Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach. Radcowie prawni, adwokaci, specjaliści prawni i&nbsp;administracyjni wspólnie organizują pracę nad sprawami.</p>
    </div>
  </div>
</section>

<section class="section about-method">
  <div class="container editorial-split">
    <h2 data-reveal>Jak pracujemy</h2>
    <dl class="method-list" data-reveal-children>
      <div>
        <dt>Rozpoznanie sprawy</dt>
        <dd>Ustalamy fakty, dokumenty i interes klienta.</dd>
      </div>
      <div>
        <dt>Dobór rozwiązania</dt>
        <dd>Przedstawiamy możliwe działania i rekomendację.</dd>
      </div>
      <div>
        <dt>Prowadzenie</dt>
        <dd>Realizujemy strategię i informujemy o postępach.</dd>
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
    <div class="editorial-list" data-reveal-children>
      <?php foreach ($site['documents'] as $doc): ?>
        <a class="editorial-row" href="/dokumenty/<?= e($doc['slug']) ?>/">
          <span class="row-title"><?= e($doc['title']) ?></span>
          <span class="row-summary"><?= e($doc['summary']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
