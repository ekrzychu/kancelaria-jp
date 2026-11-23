<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Zespół</span>
    <h1>Ludzie stojący za sprawami.</h1>
    <p>Kancelaria działa jako zespół, łącząc kompetencje prawne, koordynacyjne i administracyjne.</p>
  </div>
</section>

<section class="section" aria-label="Członkowie zespołu">
  <ul class="container people-list" role="list">
    <?php foreach ($team as $person): ?>
      <?php
      $portrait = $person['portrait'] ?? '';
      $hasPortrait = is_string($portrait)
          && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $portrait)
          && is_file(__DIR__ . '/../../public/assets/img/' . $portrait);
      ?>
      <li class="person-row">
        <div class="person-mark" aria-hidden="true">
          <?php if ($hasPortrait): ?>
            <img src="/assets/img/<?= e($portrait) ?>" alt="" loading="lazy" decoding="async">
          <?php else: ?>
            <?= e(initials($person['name'])) ?>
          <?php endif; ?>
        </div>
        <div>
          <h2><?= e($person['name']) ?></h2>
          <p><?= e($person['role']) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
