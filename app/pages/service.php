<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker"><?= e($service['category']) ?></span>
    <h1><?= e($service['title']) ?></h1>
    <p><?= e($service['summary']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container service-layout">
    <article class="rich-text service-content">
      <?php foreach ($service['body'] as $paragraph): ?>
        <p><?= e($paragraph) ?></p>
      <?php endforeach; ?>

      <h2 data-reveal>Zakres działań</h2>
      <ul class="service-scope">
        <?php foreach ($service['scope'] as $point): ?>
          <li><?= e($point) ?></li>
        <?php endforeach; ?>
      </ul>

      <div class="service-contact">
        <p>Opisz sprawę. Wrócimy z informacją o możliwym zakresie współpracy.</p>
        <a class="button" href="/kontakt/">Skontaktuj się</a>
      </div>
    </article>

    <nav class="side-index" aria-label="Specjalizacje">
      <a class="side-index-heading" href="/specjalizacje/">Wszystkie specjalizacje</a>
      <?php foreach ($services as $item): ?>
        <a href="/specjalizacje/<?= e($item['slug']) ?>/"<?= $item['slug'] === $service['slug'] ? ' aria-current="page"' : '' ?>>
          <?= e($item['title']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>
