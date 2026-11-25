<?php
$partners = array_filter($team, static fn ($person) => str_starts_with(text_lower($person['role']), 'wspólnik'));
$publications = [];
foreach ($articles as $article) {
    if (!is_array($article)) {
        continue;
    }

    $title = is_string($article['title'] ?? null) ? trim($article['title']) : '';
    $summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
    $date = $article['date'] ?? '';
    $published = is_string($date) && preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)
        ? DateTimeImmutable::createFromFormat('!Y-m-d', $date)
        : false;
    if ($title !== '' && $summary !== '' && $published && $published->format('Y-m-d') === $date) {
        $publications[] = compact('title', 'summary', 'published') + ['slug' => $article['slug'] ?? ''];
    }
}
usort($publications, static fn ($left, $right) => $right['published'] <=> $left['published']);
// Set to false until the firm confirms permission to publish the logos.
$showClientLogos = true;
$clientLogos = [
    ['bank-millennium', 'Bank Millennium', 440, 94],
    ['nest-bank', 'Nest Bank', 440, 174],
    ['ergo-hestia', 'ERGO Hestia', 317, 176],
    ['gowhistle', 'GoWhistle', 440, 115],
    ['asps-budownictwo', 'ASPS Budownictwo', 440, 67],
    ['wsb-merito', 'Uniwersytety WSB Merito', 440, 93],
    ['ultimo', 'Ultimo', 440, 142],
    ['takto-finanse', 'Takto Finanse', 200, 68],
];
// Temporary value for this experiment. Verify the experience figure before production.
$experienceYears = 6;
?>
<a class="home-contact-float" href="/kontakt/" aria-label="Kontakt z kancelarią" data-control-tone="burgundy" data-home-surface="light">
  <span class="home-contact-label">Kontakt</span>
  <span class="home-contact-face" aria-hidden="true" hidden><span class="home-contact-label">Kontakt</span></span>
</a>

<section class="home-hero" aria-labelledby="home-title" data-home-stage="hero" data-home-surface="burgundy">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="burgundy"></div>
    <div class="home-stage-content">
      <div class="home-frame home-hero-layout">
        <div class="home-hero-heading" data-reveal>
          <p class="section-kicker">Kancelaria radców prawnych we Wrocławiu</p>
          <h1 id="home-title"><span>Prawo</span> <span class="home-hero-title-accent">w&nbsp;praktyce.</span></h1>
        </div>
        <div class="home-hero-intro" data-reveal="sequence">
          <div class="home-hero-copy">
            <p class="home-hero-lead">Doradzamy firmom, instytucjom i&nbsp;klientom&nbsp;indywidualnym.</p>
            <p>Reprezentujemy ich również w&nbsp;sporach&nbsp;sądowych.</p>
          </div>
          <a class="home-hero-action" href="#specjalizacje">
            <span>Zobacz specjalizacje</span>
            <svg viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
              <path d="M2 9h24m-7-7 7 7-7 7"/>
            </svg>
          </a>
        </div>
        <picture class="home-hero-photo" data-reveal="image" data-home-surface="photo">
          <img src="/assets/img/office-reception-hero.jpg"
            alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1150" height="1480" fetchpriority="high">
        </picture>
      </div>
    </div>
  </div>
</section>

<section class="home-practices home-section" id="specjalizacje" tabindex="-1" aria-labelledby="home-practices-title" data-home-stage="practices" data-home-surface="light">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="light"></div>
    <div class="home-stage-content">
      <div class="home-frame home-practices-layout">
        <div class="home-practices-heading">
          <div class="home-practices-intro" data-reveal>
            <p class="section-kicker">Specjalizacje</p>
            <h2 id="home-practices-title">W czym możemy pomóc?</h2>
            <a class="text-action" href="/specjalizacje/">Pełna lista specjalizacji</a>
          </div>
        </div>
        <div class="home-practices-rail" data-reveal>
          <ul class="home-practice-list" role="list">
            <?php foreach (array_slice($services, 0, 8) as $service): ?>
              <?php
              $summary = $service['home_summary'] ?? $service['summary'];
              $summary = preg_replace('/(?<!\p{L})([aiouwz]) (?=\p{L})/iu', '$1' . "\u{00A0}", $summary) ?? $summary;
              ?>
              <li>
                <a class="home-practice-link" href="/specjalizacje/<?= e($service['slug']) ?>/">
                  <span><?= e($service['title']) ?></span>
                  <small><?= e($summary) ?></small>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($publications): ?>
  <section class="home-publications home-section" aria-labelledby="home-publications-title" data-home-stage="publications" data-home-surface="burgundy">
    <div class="home-stage-viewport">
      <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="burgundy"></div>
      <div class="home-stage-content">
        <div class="home-frame home-publications-layout">
          <div class="home-publications-heading" data-reveal>
            <h2 id="home-publications-title">Publikacje</h2>
            <a class="text-action" href="/artykuly/">Wszystkie publikacje</a>
          </div>
          <ol class="home-publications-list" role="list">
            <?php foreach (array_slice($publications, 0, 3) as $publication): ?>
              <li>
                <article data-reveal>
                  <time datetime="<?= e($publication['published']->format('Y-m-d')) ?>"><?= e($publication['published']->format('d.m.Y')) ?></time>
                  <h3>
                    <?php if ($publication['slug'] !== ''): ?>
                      <a href="/<?= e($publication['slug']) ?>/"><?= e($publication['title']) ?></a>
                    <?php else: ?>
                      <?= e($publication['title']) ?>
                    <?php endif; ?>
                  </h3>
                  <p><?= e($publication['summary']) ?></p>
                </article>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<section class="home-profile home-section" aria-labelledby="home-profile-title" data-home-stage="profile" data-home-surface="beige">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="beige"></div>
    <div class="home-stage-content">
      <div class="home-frame home-profile-layout" data-home-surface="beige">
        <div class="home-profile-statement" data-reveal>
          <p class="section-kicker">O kancelarii</p>
          <h2 id="home-profile-title">Najpierw sprawa.<br>Potem strategia.</h2>
        </div>
        <div class="home-profile-side">
          <div class="home-profile-detail" data-reveal>
            <p>Ustalamy, co jest dla klienta najważniejsze, i&nbsp;przedstawiamy możliwe działania oraz ich konsekwencje. Wspieramy w&nbsp;pojedynczych sprawach i&nbsp;w&nbsp;stałej współpracy.</p>
            <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię</a>
          </div>
          <p class="home-experience" data-reveal="experience">
            <span class="visually-hidden"><?= $experienceYears ?>+</span>
            <span class="home-experience-value" aria-hidden="true">
              <span class="home-experience-number" data-final-value="<?= $experienceYears ?>"><span><?= $experienceYears ?></span></span><span class="home-experience-plus">+</span>
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
    </div>
  </div>
</section>

<section class="home-office home-section" aria-labelledby="home-office-title" data-home-stage="office" data-home-surface="light">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true"></div>
    <div class="home-stage-content">
      <div class="home-frame home-office-layout">
        <picture class="home-office-photo" data-reveal="image" data-scroll-photo data-home-surface="photo">
          <img src="/assets/img/office-entrance-1280.webp"
            srcset="/assets/img/office-entrance-760.webp 760w, /assets/img/office-entrance-1280.webp 1280w, /assets/img/office-entrance-2240.webp 2240w"
            sizes="(max-width: 42rem) 90vw, (max-width: 56rem) 49vw, (min-width: 129rem) 66rem, 55vw"
            alt="Drewniane drzwi wejściowe i numer 7–8 na kamiennej fasadzie przy placu Kościuszki" width="1280" height="1990" loading="lazy" decoding="async">
        </picture>
        <div class="home-office-copy" data-reveal data-home-surface="beige">
          <p class="section-kicker">Siedziba kancelarii</p>
          <h2 id="home-office-title">Wrocław.</h2>
          <address><?= implode('<br>', array_map('e', $site['address'])) ?></address>
          <a class="text-action" href="/kontakt/">Dane kontaktowe i godziny pracy</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($showClientLogos): ?>
  <section class="home-clients" aria-labelledby="home-clients-title" data-home-stage="clients" data-home-surface="beige">
    <div class="home-stage-viewport">
      <div class="home-stage-backdrop" aria-hidden="true" data-home-surface="beige"></div>
      <div class="home-stage-content">
        <div class="home-frame home-clients-layout">
          <h2 id="home-clients-title" data-reveal>Współpracujemy z</h2>
          <ul class="home-client-logos" data-reveal role="list">
            <?php foreach ($clientLogos as [$slug, $name, $width, $height]): ?>
              <li class="home-client-<?= e($slug) ?>">
                <img src="/assets/img/clients/<?= e($slug) ?>.webp" alt="<?= e($name) ?>" width="<?= $width ?>" height="<?= $height ?>" loading="lazy" decoding="async">
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>
