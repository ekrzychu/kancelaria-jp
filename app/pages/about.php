<section class="about-hero" aria-labelledby="about-title">
  <div class="about-frame about-hero-layout">
    <div class="about-hero-copy" data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title">Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <svg class="about-monogram" viewBox="0 0 640 380" aria-hidden="true" focusable="false">
      <path fill="var(--line)" d="M258 48v148c0 79-55 136-132 136H78l57-57c38-8 63-35 63-79v-88Z"/>
      <path fill="var(--paper)" d="M258 48v148c0 79-55 136-132 136H78l28-28h20c61 0 104-45 104-108V76Z"/>
      <path fill="var(--taupe)" d="M290 108l60-60v224l-60 60Z"/>
      <path fill="var(--line)" d="M382 48h40c83 0 140 39 140 92s-57 92-140 92h-40Z"/>
      <path fill="var(--paper)" d="M382 48h40c83 0 140 39 140 92 0 10-2 20-6 29-15-54-71-88-134-88h-40Z"/>
    </svg>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame">
    <div class="about-identity-copy" data-reveal>
      <h2 class="section-kicker" id="about-identity-title">Kim jesteśmy</h2>
      <p class="about-lead">Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      <p class="about-identity-detail">Radcowie prawni, adwokaci, specjaliści prawni i&nbsp;administracyjni wspólnie organizują pracę nad sprawami.</p>
    </div>
  </div>
</section>

<section class="about-method" aria-labelledby="about-method-title" data-home-surface="beige">
  <div class="about-frame about-method-layout">
    <h2 class="about-section-title" id="about-method-title">Jak pracujemy</h2>
    <ol class="about-steps" role="list" data-reveal-sequence>
      <li class="about-step" data-reveal>
        <span class="about-step-index" aria-hidden="true">01</span>
        <h3>Rozpoznanie sprawy</h3>
        <p>Ustalamy fakty, dokumenty i interes klienta.</p>
      </li>
      <li class="about-step" data-reveal>
        <span class="about-step-index" aria-hidden="true">02</span>
        <h3>Dobór rozwiązania</h3>
        <p>Przedstawiamy możliwe działania i rekomendację.</p>
      </li>
      <li class="about-step" data-reveal>
        <span class="about-step-index" aria-hidden="true">03</span>
        <h3>Prowadzenie</h3>
        <p>Realizujemy strategię i informujemy o postępach.</p>
      </li>
    </ol>
  </div>
</section>

<section class="about-documents" aria-labelledby="about-documents-title">
  <div class="about-frame">
    <div class="about-documents-intro" data-reveal>
      <span class="section-kicker">Dokumenty</span>
      <h2 class="about-section-title" id="about-documents-title">Standardy i&nbsp;informacje formalne.</h2>
    </div>
    <ul class="about-document-list" role="list" data-reveal-sequence>
      <?php foreach ($site['documents'] as $doc): ?>
        <li>
          <a class="about-document" href="/dokumenty/<?= e($doc['slug']) ?>/" data-reveal>
            <span class="row-title"><?= e($doc['title']) ?></span>
            <span class="row-summary"><?= e($doc['summary']) ?></span>
            <span class="about-document-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
