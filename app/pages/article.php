<article class="container article-detail">
  <header>
    <a class="text-action" href="/artykuly/">Wszystkie publikacje</a>
    <p class="publication-meta"><time datetime="<?= e($article['date']) ?>"><?= e((new DateTimeImmutable($article['date']))->format('d.m.Y')) ?></time></p>
    <h1><?= e($article['title']) ?></h1>
  </header>
  <div class="rich-text">
    <?php // Reviewed editorial HTML imported from the firm's local publication dataset. ?>
    <?= $article['body_html'] ?>
  </div>
</article>
