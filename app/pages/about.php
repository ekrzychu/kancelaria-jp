<section class="about-hero" aria-labelledby="about-title">
  <div class="about-frame about-grid">
    <div class="about-hero-copy" data-scroll-shift>
      <span class="section-kicker" data-reveal>O kancelarii</span>
      <h1 id="about-title" data-scroll-shift-content data-reveal>Kancelaria tworzona przez praktyków.</h1>
      <p data-reveal>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <figure class="about-hero-figure" data-scroll-photo>
      <picture class="about-photo" data-reveal="image">
        <source type="image/webp" srcset="/assets/img/building-exterior-800.webp 800w, /assets/img/building-exterior-wide.webp 1800w" sizes="(min-width: 152rem) 77rem, (min-width: 48rem) 52vw, 90vw">
        <img src="/assets/img/building-exterior-wide.jpg" alt="Kamienna fasada z kolumnami" width="1800" height="980" fetchpriority="high">
      </picture>
      <figcaption>Wrocław — siedziba kancelarii</figcaption>
    </figure>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame about-grid">
    <h2 class="section-kicker" id="about-identity-title" data-reveal>Kim jesteśmy</h2>
    <div class="about-identity-lead" data-scroll-shift>
      <p class="about-lead" data-scroll-shift-content data-reveal>Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
    </div>
    <p class="about-identity-detail" data-reveal>Radcowie prawni, adwokaci, specjaliści prawni i&nbsp;administracyjni wspólnie organizują pracę nad sprawami.</p>
    <div class="about-photo about-identity-photo" data-reveal="image">
      <img src="/assets/img/office-meeting-760.webp" srcset="/assets/img/office-meeting-760.webp 760w, /assets/img/office-meeting-1360.webp 1360w" sizes="(min-width: 152rem) 55rem, (min-width: 48rem) 38vw, 90vw" alt="Sala spotkań kancelarii z drewnianym stołem" width="760" height="445" loading="lazy" decoding="async">
    </div>
  </div>
</section>

<section class="about-method" aria-labelledby="about-method-title" data-home-surface="beige">
  <div class="about-frame about-grid">
    <h2 class="about-section-title" id="about-method-title" data-reveal>Jak pracujemy</h2>
    <dl class="about-steps" data-about-steps>
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

<section class="about-documents" aria-labelledby="about-documents-title">
  <div class="about-frame">
    <div class="about-documents-intro" data-reveal>
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
