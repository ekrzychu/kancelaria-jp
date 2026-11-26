<?php
$publications = array_filter($articles, static fn ($entry) =>
    is_array($entry) && is_string($entry['title'] ?? null) && trim($entry['title']) !== ''
);
usort($publications, static fn (array $a, array $b): int =>
    (publication_date($b['date'] ?? null)?->format('Y-m-d') ?? '')
    <=> (publication_date($a['date'] ?? null)?->format('Y-m-d') ?? '')
);
?>
<section class="publication-hero" aria-labelledby="publications-title">
  <div class="publication-frame publication-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">Artykuły</span>
      <h1 id="publications-title">Publikacje prawne.</h1>
    </div>
    <p data-reveal="subtle">Analizy i&nbsp;komentarze do zmian w&nbsp;prawie.</p>
  </div>
</section>

<section class="publication-archive<?= $publications ? '' : ' archive-empty' ?>" aria-label="Publikacje">
  <div class="publication-frame" data-reveal-sequence>
    <?php if ($publications): ?>
      <?php foreach ($publications as $index => $publication): ?>
        <?php
        $featured = $index === 0 && publication_date($publication['date'] ?? null) !== null;
        $summary = is_string($publication['summary'] ?? null) ? trim($publication['summary']) : '';
        $image = is_string($publication['image'] ?? null) ? $publication['image'] : '';
        $imageAlt = is_string($publication['image_alt'] ?? null) ? $publication['image_alt'] : '';
        $imagePath = __DIR__ . '/../../public/assets/img/' . $image;
        $imageSize = preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $image)
            && is_file($imagePath)
            ? getimagesize($imagePath) : false;
        $classes = 'publication-entry' . ($featured ? ' is-featured' : '')
            . ($summary !== '' ? ' has-summary' : '') . ($imageSize ? ' has-image' : '');
        ?>
        <article class="<?= e($classes) ?>" data-reveal="subtle">
          <div class="publication-heading">
            <?php if ($featured): ?>
              <p class="section-kicker">Najnowsza publikacja</p>
            <?php endif; ?>
            <?php require __DIR__ . '/../templates/publication-meta.php'; ?>
            <h2>
              <?php if (!empty($publication['slug'])): ?>
                <a class="publication-title-link" href="/<?= e($publication['slug']) ?>/"><span><?= e($publication['title']) ?></span>&nbsp;<svg class="publication-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 9h24m-7-7 7 7-7 7"/></svg></a>
              <?php else: ?>
                <?= e($publication['title']) ?>
              <?php endif; ?>
            </h2>
          </div>
          <?php if ($summary !== ''): ?>
            <p class="publication-summary"><?= e($summary) ?></p>
          <?php endif; ?>
          <?php if ($imageSize): ?>
            <img class="publication-image" src="/assets/img/<?= e($image) ?>" alt="<?= e($imageAlt) ?>" width="<?= $imageSize[0] ?>" height="<?= $imageSize[1] ?>" loading="<?= $featured ? 'eager' : 'lazy' ?>" decoding="async">
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="archive-note" data-reveal="subtle">
        <h2>Archiwum w przygotowaniu</h2>
        <p>W sprawie konkretnego zagadnienia napisz lub zadzwoń.</p>
        <a class="text-action" href="/kontakt/">Skontaktuj się</a>
      </div>
    <?php endif; ?>
  </div>
</section>
