<section class="about-hero" aria-labelledby="about-title">
  <div class="container about-hero-grid">
    <div class="about-hero-copy" data-reveal data-scroll-shift>
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title" data-scroll-shift-content>Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <picture class="about-photo" data-reveal="image" data-scroll-photo>
      <source type="image/webp" srcset="/assets/img/building-exterior-800.webp 800w, /assets/img/building-exterior-wide.webp 1800w" sizes="(min-width: 140rem) 112rem, 91vw">
      <img src="/assets/img/building-exterior-wide.jpg" alt="Detal fasady siedziby kancelarii we Wrocławiu" width="1800" height="980" fetchpriority="high">
    </picture>
  </div>
</section>

<section class="about-section about-identity" aria-labelledby="about-identity-title">
  <div class="container about-editorial-grid">
    <h2 id="about-identity-title" data-reveal>Kim jesteśmy</h2>
    <div class="about-identity-copy" data-scroll-shift>
      <p class="about-lead" data-scroll-shift-content data-reveal>Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      <p class="about-identity-detail" data-reveal>Radcowie prawni, adwokaci, specjaliści prawni i&nbsp;administracyjni wspólnie organizują pracę nad sprawami.</p>
    </div>
  </div>
</section>

<section class="about-section about-method" aria-labelledby="about-method-title" data-home-surface="beige">
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
