<?php
$publications = array_filter($articles, static fn ($article) =>
    is_array($article) && is_string($article['title'] ?? null) && trim($article['title']) !== ''
);
?>
<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Artykuły</span>
    <h1>Publikacje prawne.</h1>
    <p>Analizy i&nbsp;komentarze do zmian w&nbsp;prawie.</p>
  </div>
</section>

<section class="section<?= $publications ? '' : ' archive-empty' ?>">
  <?php if ($publications): ?>
    <div class="container editorial-list" aria-label="Publikacje" data-reveal-children>
      <?php foreach ($publications as $article): ?>
        <?php
        $date = $article['date'] ?? '';
        $published = is_string($date) && preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $date)
            : false;
        $hasDate = $published && $published->format('Y-m-d') === $date;
        $category = is_string($article['category'] ?? null) ? trim($article['category']) : '';
        $summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
        $image = $article['image'] ?? '';
        $imageAlt = is_string($article['image_alt'] ?? null) ? $article['image_alt'] : '';
        $hasImage = is_string($image)
            && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $image)
            && is_file(__DIR__ . '/../../public/assets/img/' . $image);
        ?>
        <article class="publication-entry<?= $hasImage ? ' has-image' : '' ?>">
          <div>
            <?php if ($hasDate || $category !== ''): ?>
              <p class="publication-meta">
                <?php if ($hasDate): ?>
                  <time datetime="<?= e($date) ?>"><?= e($published->format('d.m.Y')) ?></time>
                <?php endif; ?>
                <?php if ($category !== ''): ?>
                  <span><?= e($category) ?></span>
                <?php endif; ?>
              </p>
            <?php endif; ?>
            <h2>
              <?php if (!empty($article['slug'])): ?>
                <a href="/<?= e($article['slug']) ?>/"><?= e($article['title']) ?></a>
              <?php else: ?>
                <?= e($article['title']) ?>
              <?php endif; ?>
            </h2>
            <?php if ($summary !== ''): ?>
              <p class="publication-summary"><?= e($summary) ?></p>
            <?php endif; ?>
          </div>
          <?php if ($hasImage): ?>
            <img src="/assets/img/<?= e($image) ?>" alt="<?= e($imageAlt) ?>" loading="lazy" decoding="async">
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="container">
      <div class="archive-note" data-reveal>
        <h2>Archiwum w przygotowaniu</h2>
        <p>W sprawie konkretnego zagadnienia napisz lub zadzwoń.</p>
        <a class="text-action" href="/kontakt/">Skontaktuj się</a>
      </div>
    </div>
  <?php endif; ?>
</section>
