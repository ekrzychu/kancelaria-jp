<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">Dokumenty</span>
    <h1>Dokumenty i informacje formalne.</h1>
    <p>Wybrane dokumenty kancelarii zebrane w jednym miejscu.</p>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container document-list">
    <?php foreach ($site['documents'] as $doc): ?>
      <a class="document-row" href="/dokumenty/<?= e($doc['slug']) ?>/">
        <strong><?= e($doc['title']) ?></strong>
        <span><?= e($doc['summary']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
