<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">Zespół</span>
    <h1>Ludzie stojący za sprawami.</h1>
    <p>Kancelaria działa jako zespół, łącząc kompetencje prawne, koordynacyjne i administracyjne.</p>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container people-list">
    <?php foreach ($team as $person): ?>
      <article class="person-row">
        <span><?= e(initials($person['name'])) ?></span>
        <strong><?= e($person['name']) ?></strong>
        <em><?= e($person['role']) ?></em>
      </article>
    <?php endforeach; ?>
  </div>
</section>
