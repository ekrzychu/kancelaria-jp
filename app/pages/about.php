<section class="about-hero" aria-labelledby="about-title">
  <div class="container about-hero-grid">
    <div class="about-hero-copy" data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title">Kancelaria <span>tworzona przez</span> <span class="about-title-accent">praktyków.</span></h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <picture class="about-photo" data-reveal="image" data-scroll-photo>
      <source type="image/webp" srcset="/assets/img/building-exterior-portrait-760.webp 760w, /assets/img/building-exterior-portrait.webp 1000w" sizes="(max-width: 42rem) 100vw, (min-width: 112rem) 48rem, 42vw">
      <img src="/assets/img/building-exterior-portrait.jpg" alt="Detal fasady siedziby kancelarii we Wrocławiu" width="1000" height="1300" fetchpriority="high">
    </picture>
  </div>
</section>

<section class="about-section about-identity" aria-labelledby="about-identity-title">
  <div class="container about-editorial-grid">
    <h2 id="about-identity-title" data-reveal>Kim jesteśmy</h2>
    <div class="about-identity-copy">
      <p class="about-lead" data-reveal>Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      <p class="about-identity-detail" data-reveal>Radcowie prawni, adwokaci, specjaliści prawni i&nbsp;administracyjni wspólnie organizują pracę nad sprawami.</p>
    </div>
  </div>
</section>

<section class="about-section about-method" aria-labelledby="about-method-title">
  <div class="container about-editorial-grid">
    <h2 class="about-section-title" id="about-method-title" data-reveal>Jak pracujemy</h2>
    <dl class="about-steps" data-reveal-children>
      <div class="about-step">
        <dt><span class="about-step-index" aria-hidden="true">01</span> Rozpoznanie sprawy</dt>
        <dd>Ustalamy fakty, dokumenty i interes klienta.</dd>
      </div>
      <div class="about-step">
        <dt><span class="about-step-index" aria-hidden="true">02</span> Dobór rozwiązania</dt>
        <dd>Przedstawiamy możliwe działania i rekomendację.</dd>
      </div>
      <div class="about-step">
        <dt><span class="about-step-index" aria-hidden="true">03</span> Prowadzenie</dt>
        <dd>Realizujemy strategię i informujemy o postępach.</dd>
      </div>
    </dl>
  </div>
</section>

<section class="about-section about-documents" aria-labelledby="about-documents-title">
  <div class="container about-editorial-grid">
    <div data-reveal>
      <span class="section-kicker">Dokumenty</span>
      <h2 class="about-section-title" id="about-documents-title">Standardy i&nbsp;informacje formalne.</h2>
    </div>
    <ul class="about-document-list" role="list" data-reveal-children>
      <?php foreach ($site['documents'] as $doc): ?>
        <li>
          <a class="about-document" href="/dokumenty/<?= e($doc['slug']) ?>/">
            <span class="row-title"><?= e($doc['title']) ?></span>
            <span class="row-summary"><?= e($doc['summary']) ?></span>
            <span class="about-document-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
