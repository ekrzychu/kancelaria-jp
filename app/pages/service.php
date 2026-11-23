<section class="page-hero">
  <div class="container narrow">
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

      <h2>Zakres działań</h2>
      <ul>
        <?php foreach ($service['scope'] as $point): ?>
          <li><?= e($point) ?></li>
        <?php endforeach; ?>
      </ul>

      <p class="contact-note">Potrzebujesz wsparcia w tym obszarze? Opisz krótko sprawę — kancelaria wróci z informacją o możliwym zakresie współpracy.</p>
      <p><a class="button" href="/kontakt/">Skontaktuj się</a></p>
    </article>

    <nav class="side-index" aria-label="Specjalizacje">
      <span>Specjalizacje</span>
      <?php foreach ($services as $item): ?>
        <a href="/specjalizacje/<?= e($item['slug']) ?>/"<?= $item['slug'] === $service['slug'] ? ' aria-current="page"' : '' ?>>
          <?= e($item['title']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>
