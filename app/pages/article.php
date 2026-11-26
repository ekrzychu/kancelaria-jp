<?php
$publication = $article;
$summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
$archiveLink = <<<'HTML'
<a class="text-action publication-back" href="/artykuly/">
  <svg class="publication-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M26 9H2m7-7L2 9l7 7"/></svg>
  <span>Wszystkie artykuły</span>
</a>
HTML;
?>
<article class="article-detail publication-frame" aria-labelledby="article-title">
  <header class="article-header" data-reveal="subtle">
    <?= $archiveLink ?>
    <div class="article-intro<?= $summary !== '' ? ' has-summary' : '' ?>">
      <div>
        <?php require __DIR__ . '/../templates/publication-meta.php'; ?>
        <h1 id="article-title"><?= e($article['title']) ?></h1>
      </div>
      <?php if ($summary !== ''): ?>
        <p class="article-summary"><?= e($summary) ?></p>
      <?php endif; ?>
    </div>
  </header>
  <div class="article-body">
    <?php // Reviewed editorial HTML imported from the firm's local publication dataset. ?>
    <?= $article['body_html'] ?>
  </div>
  <nav class="article-navigation" aria-label="Nawigacja po artykułach">
    <?= $archiveLink ?>
  </nav>
</article>
