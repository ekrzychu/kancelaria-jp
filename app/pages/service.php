<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker"><?= e($service['category']) ?></span>
    <h1><?= e($service['title']) ?></h1>
    <p><?= e($service['summary']) ?></p>
  </div>
</section>

<section class="section page-reveal">
  <div class="container service-layout">
    <aside class="side-index">
      <span>Specjalizacje</span>
      <?php foreach ($services as $item): ?>
        <?php $currentClass = $item['slug'] === $service['slug'] ? 'current' : ''; ?>
        <a class="<?= $currentClass ?>" href="/specjalizacje/<?= e($item['slug']) ?>/">
          <?= e($item['title']) ?>
        </a>
      <?php endforeach; ?>
    </aside>

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
  </div>
</section>
