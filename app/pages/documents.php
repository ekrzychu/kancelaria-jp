<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Dokumenty</span>
    <h1>Dokumenty i informacje formalne.</h1>
    <p>Wybrane dokumenty kancelarii zebrane w jednym miejscu.</p>
  </div>
</section>

<section class="section" aria-label="Lista dokumentów">
  <div class="container editorial-list">
    <?php foreach ($site['documents'] as $doc): ?>
      <a class="editorial-row" href="/dokumenty/<?= e($doc['slug']) ?>/">
        <span class="row-title"><?= e($doc['title']) ?></span>
        <span class="row-summary"><?= e($doc['summary']) ?></span>
        <span class="row-indicator" aria-hidden="true"><?= icon('arrow', 'inline-arrow') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
