<?php
$partners = array_filter($team, static fn ($person) => str_starts_with(text_lower($person['role']), 'wspólnik'));
$publications = array_filter($articles, static fn ($article) =>
    is_array($article) && is_string($article['title'] ?? null) && trim($article['title']) !== ''
);
// Temporary value for this experiment. Verify the experience figure before production.
$experienceYears = 6;
?>
<a class="home-contact-float" href="/kontakt/" aria-label="Kontakt z kancelarią">Kontakt</a>

<section class="home-hero" aria-labelledby="home-title">
  <div class="home-frame home-hero-layout">
    <div class="home-hero-heading">
      <p class="section-kicker">Kancelaria Radców Prawnych · Wrocław</p>
      <h1 id="home-title">Prawo <span>w&nbsp;praktyce.</span></h1>
    </div>
    <picture class="home-hero-photo" data-scroll-photo="hero">
      <img src="/assets/img/office-meeting-1360.webp"
        srcset="/assets/img/office-meeting-760.webp 760w, /assets/img/office-meeting-1360.webp 1360w, /assets/img/office-meeting-2048.webp 2048w, /assets/img/office-meeting-2880.webp 2880w"
        sizes="(max-width: 56rem) 95vw, 71vw"
        alt="Sala spotkań kancelarii z drewnianym stołem i monogramem JP" width="1360" height="796" fetchpriority="high">
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
    <div class="home-profile-side">
      <div class="home-profile-detail" data-reveal>
        <p>Ustalamy, co jest dla klienta najważniejsze, i&nbsp;przedstawiamy możliwe działania oraz ich konsekwencje. Wspieramy w&nbsp;pojedynczych sprawach i&nbsp;w&nbsp;stałej współpracy.</p>
        <a class="text-action" href="/o-kancelarii/">Jak pracujemy</a>
      </div>
      <p class="home-experience" data-reveal="count">
        <span class="visually-hidden"><?= $experienceYears ?>+</span>
        <span class="home-experience-value" aria-hidden="true">
          <span class="home-experience-number" data-experience="<?= $experienceYears ?>"><span><?= $experienceYears ?></span></span><span class="home-experience-plus">+</span>
        </span>
        <span>lat doświadczenia</span>
      </p>
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
    <picture class="home-office-photo" data-reveal="image" data-scroll-photo>
      <img src="/assets/img/office-entrance-1280.webp"
        srcset="/assets/img/office-entrance-760.webp 760w, /assets/img/office-entrance-1280.webp 1280w, /assets/img/office-entrance-2240.webp 2240w"
        sizes="(max-width: 42rem) 90vw, (max-width: 56rem) 49vw, (min-width: 129rem) 66rem, 55vw"
        alt="Drewniane drzwi wejściowe i numer 7–8 na kamiennej fasadzie przy placu Kościuszki" width="1280" height="1990" loading="lazy" decoding="async">
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

<section class="home-contact home-section" aria-labelledby="home-contact-title">
  <div class="home-frame home-contact-layout" data-reveal="sequence">
    <h2 id="home-contact-title">Każda sprawa zaczyna się od&nbsp;rozmowy.</h2>
    <div class="home-contact-detail">
      <p>Przedstaw nam swoją sytuację. Pomożemy ustalić możliwe kierunki dalszego działania.</p>
      <a class="button home-contact-action" href="/kontakt/">Skontaktuj się z&nbsp;kancelarią</a>
    </div>
  </div>
</section>
