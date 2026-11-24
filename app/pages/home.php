<?php
$partners = array_filter($team, static fn ($person) => str_starts_with(text_lower($person['role']), 'wspólnik'));
$publications = array_filter($articles, static fn ($article) =>
    is_array($article) && is_string($article['title'] ?? null) && trim($article['title']) !== ''
);
?>
<section class="home-hero" aria-labelledby="home-title">
  <div class="home-frame home-hero-layout">
    <div class="home-hero-heading">
      <p class="section-kicker">Kancelaria Radców Prawnych · Wrocław</p>
      <h1 id="home-title">Prawo <span>w&nbsp;praktyce.</span></h1>
    </div>
    <picture class="home-hero-photo">
      <source media="(max-width: 56rem)" type="image/webp" srcset="/assets/img/office-reception-mobile.webp" width="840" height="560">
      <source type="image/webp" srcset="/assets/img/office-reception-wide.webp" width="1800" height="900">
      <img src="/assets/img/office-reception-wide.jpg" alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1800" height="900" fetchpriority="high">
    </picture>
    <div class="home-hero-intro">
      <p>Doradzamy firmom, instytucjom i&nbsp;klientom indywidualnym. Reprezentujemy ich również w&nbsp;sporach sądowych.</p>
      <a class="button home-hero-action" href="#specjalizacje">Zobacz specjalizacje</a>
    </div>
  </div>
</section>

<section class="home-practices home-section" id="specjalizacje" tabindex="-1" aria-labelledby="home-practices-title">
  <div class="home-frame home-practices-layout">
    <div class="home-practices-heading" data-reveal="sequence">
      <p class="section-kicker">Specjalizacje</p>
      <h2 id="home-practices-title">W czym możemy pomóc?</h2>
      <a class="text-action" href="/specjalizacje/">Pełna lista specjalizacji</a>
    </div>
    <ul class="home-practice-list" role="list">
      <?php foreach (array_slice($services, 0, 8) as $service): ?>
        <?php
        $summary = $service['home_summary'] ?? $service['summary'];
        $summary = preg_replace('/(?<!\p{L})([aiouwz]) (?=\p{L})/iu', '$1' . "\u{00A0}", $summary) ?? $summary;
        ?>
        <li data-reveal="row">
          <a class="home-practice-link" href="/specjalizacje/<?= e($service['slug']) ?>/">
            <span><?= e($service['title']) ?></span>
            <small><?= e($summary) ?></small>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="home-profile home-section" aria-labelledby="home-profile-title">
  <div class="home-frame home-profile-layout">
    <div class="home-profile-statement" data-reveal="sequence">
      <p class="section-kicker">O kancelarii</p>
      <h2 id="home-profile-title">Najpierw sprawa.<br>Potem strategia.</h2>
    </div>
    <div class="home-profile-detail" data-reveal>
      <p>Ustalamy, co jest dla klienta najważniejsze, i&nbsp;przedstawiamy możliwe działania oraz ich konsekwencje. Wspieramy w&nbsp;pojedynczych sprawach i&nbsp;w&nbsp;stałej współpracy.</p>
      <a class="text-action" href="/o-kancelarii/">Jak pracujemy</a>
    </div>
    <div class="home-team" data-reveal>
      <div>
        <h3>Zespół kancelarii</h3>
        <a class="text-action" href="/zespol/">Poznaj cały zespół</a>
      </div>
      <?php if ($partners): ?>
        <ul role="list">
          <?php foreach (array_slice($partners, 0, 2) as $person): ?>
            <li><p><?= e($person['name']) ?></p><span><?= e($person['role']) ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="home-office home-section" aria-labelledby="home-office-title">
  <div class="home-frame home-office-layout">
    <picture class="home-office-photo" data-reveal="image">
      <source type="image/webp" srcset="/assets/img/building-exterior-800.webp 800w, /assets/img/building-exterior-wide.webp 1800w" sizes="(max-width: 42rem) 95vw, (max-width: 56rem) 53vw, (min-width: 150rem) 86rem, 57vw">
      <img src="/assets/img/building-exterior-wide.jpg" alt="Kamienna fasada budynku kancelarii przy placu Kościuszki we Wrocławiu" width="1800" height="980" loading="lazy" decoding="async">
    </picture>
    <div class="home-office-copy" data-reveal>
      <p class="section-kicker">Siedziba kancelarii</p>
      <h2 id="home-office-title">Wrocław.</h2>
      <address><?= implode('<br>', array_map('e', $site['address'])) ?></address>
      <a class="text-action" href="/kontakt/">Dane kontaktowe i godziny pracy</a>
    </div>
  </div>
</section>

<?php if ($publications): ?>
  <section class="home-publications home-section" aria-labelledby="home-publications-title">
    <div class="home-frame home-publications-layout">
      <div data-reveal>
        <h2 id="home-publications-title">Publikacje</h2>
        <a class="text-action" href="/artykuly/">Wszystkie publikacje</a>
      </div>
      <ul role="list" data-reveal>
        <?php foreach (array_slice($publications, 0, 3) as $article): ?>
          <li><h3><?= e($article['title']) ?></h3></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>
