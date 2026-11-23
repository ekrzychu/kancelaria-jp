<?php
$teamGroups = ['leadership' => [], 'coordinators' => [], 'team' => []];
foreach ($team as $person) {
    $role = text_lower($person['role']);
    $group = match (true) {
        str_starts_with($role, 'wspólnik'), str_starts_with($role, 'dyrektor') => 'leadership',
        str_starts_with($role, 'koordynator') => 'coordinators',
        default => 'team',
    };
    $teamGroups[$group][] = $person;
}
?>
<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Zespół</span>
    <h1>Ludzie stojący za sprawami.</h1>
  </div>
</section>

<section class="section" aria-label="Członkowie zespołu">
  <div class="container people-groups">
    <?php foreach (array_filter($teamGroups) as $group => $people): ?>
      <ul class="people-list people-list--<?= e($group) ?>" role="list">
        <?php foreach ($people as $person): ?>
          <?php
          $portrait = $person['portrait'] ?? '';
          $hasPortrait = is_string($portrait)
              && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $portrait)
              && is_file(__DIR__ . '/../../public/assets/img/' . $portrait);
          ?>
          <li class="person-row<?= $hasPortrait ? ' has-portrait' : '' ?>">
            <?php if ($hasPortrait): ?>
              <img class="person-portrait" src="/assets/img/<?= e($portrait) ?>" alt="" loading="lazy" decoding="async">
            <?php endif; ?>
            <div>
              <h2><?= e($person['name']) ?></h2>
              <p><?= e($person['role']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>
  </div>
</section>
