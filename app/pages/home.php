<section class="home-hero">
  <div class="home-hero-grid">
    <div class="home-hero-copy">
      <p class="section-kicker">Kancelaria Radców Prawnych · Wrocław</p>
      <h1>Prawo prowadzone spokojnie, precyzyjnie <span>i&nbsp;skutecznie.</span></h1>
      <p class="home-hero-lead">Doradzamy firmom, instytucjom i&nbsp;klientom indywidualnym. Łączymy jasną strategię z&nbsp;doświadczeniem procesowym.</p>
      <a class="button" href="/specjalizacje/">Zobacz specjalizacje</a>
    </div>
    <figure class="home-hero-photo">
      <picture>
        <source media="(max-width: 42rem)" type="image/webp" srcset="/assets/img/office-reception-mobile.webp" width="840" height="560">
        <source type="image/webp" srcset="/assets/img/office-reception-800.webp 800w, /assets/img/office-reception-hero.webp 1150w" sizes="49vw">
        <img src="/assets/img/office-reception-hero.jpg" alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1150" height="1480" fetchpriority="high">
      </picture>
    </figure>
  </div>
</section>

<section class="home-section home-practices">
  <div class="container home-practices-grid">
    <div data-reveal>
      <p class="section-kicker">Specjalizacje</p>
      <h2>W czym pomagamy</h2>
      <a class="text-action" href="/specjalizacje/">Wszystkie specjalizacje</a>
    </div>
    <ul class="home-practice-list" role="list">
      <?php foreach (array_slice($services, 0, 8) as $service): ?>
        <li><a class="home-practice-link" href="/specjalizacje/<?= e($service['slug']) ?>/"><?= e($service['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="home-section home-about">
  <div class="container">
    <div class="editorial-split">
      <div data-reveal>
        <p class="section-kicker">O kancelarii</p>
        <h2>Formalnie tam, gdzie trzeba. Praktycznie tam, gdzie to&nbsp;możliwe.</h2>
      </div>
      <div class="rich-text">
        <p>Łączymy doświadczenie w&nbsp;obsłudze firm i&nbsp;rynku finansowego z&nbsp;praktyką w&nbsp;sprawach rodzinnych, spadkowych i&nbsp;sądowych. Wspieramy klientów stale lub w&nbsp;konkretnej sprawie.</p>
        <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię</a>
      </div>
    </div>
    <dl class="home-standards-list" aria-label="Jak pracujemy">
      <div>
        <dt>Analiza</dt>
        <dd>Porządkujemy fakty, dokumenty i&nbsp;cel sprawy.</dd>
      </div>
      <div>
        <dt>Strategia</dt>
        <dd>Dobieramy działania do ryzyka, czasu i&nbsp;oczekiwanego rezultatu.</dd>
      </div>
      <div>
        <dt>Komunikacja</dt>
        <dd>Omawiamy etapy i&nbsp;decyzje wymagające udziału klienta.</dd>
      </div>
    </dl>
  </div>
</section>

<section class="home-team">
  <div class="container" data-reveal>
    <h2>Radcowie prawni, adwokaci i&nbsp;specjaliści.</h2>
    <a class="text-action" href="/zespol/">Poznaj zespół</a>
  </div>
</section>

<section class="home-section home-location">
  <div class="container home-location-grid">
    <figure class="home-location-photo" data-reveal="image">
      <picture>
        <source type="image/webp" srcset="/assets/img/building-exterior-800.webp 800w, /assets/img/building-exterior-wide.webp 1800w" sizes="(max-width: 56rem) 91vw, (min-width: 91rem) 62rem, 69vw">
        <img src="/assets/img/building-exterior-wide.jpg" alt="Kamienna fasada budynku kancelarii przy placu Kościuszki we Wrocławiu" width="1800" height="980" loading="lazy" decoding="async">
      </picture>
    </figure>
    <div class="home-location-copy">
      <p class="section-kicker">Siedziba kancelarii</p>
      <h2>W centrum Wrocławia.</h2>
      <address class="home-address"><?= implode('<br>', array_map('e', $site['address'])) ?></address>
      <a class="text-action" href="/kontakt/">Dane kontaktowe</a>
    </div>
  </div>
</section>

<div class="container home-section home-resources">
  <section>
    <h2>Standardy i&nbsp;dokumenty</h2>
    <a class="text-action" href="/dokumenty/">Zobacz dokumenty</a>
  </section>
  <section>
    <h2>Artykuły i&nbsp;komentarze prawne</h2>
    <?php if (!$articles): ?>
      <p>Archiwum w przygotowaniu.</p>
    <?php endif; ?>
    <a class="text-action" href="/artykuly/">Przejdź do artykułów</a>
  </section>
</div>
