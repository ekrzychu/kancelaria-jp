<?php
$teamGroups = [
    'leadership' => ['heading' => 'Wspólnicy i dyrekcja', 'people' => []],
    'coordinators' => ['heading' => 'Koordynatorzy zespołu', 'people' => []],
    'lawyers' => ['heading' => 'Prawnicy', 'people' => []],
    'support' => ['heading' => 'Specjaliści i administracja', 'people' => []],
];
foreach ($team as $person) {
    [$title] = explode(' · ', text_lower($person['role']), 2);
    $group = match (true) {
        str_starts_with($title, 'wspólnik'), str_starts_with($title, 'dyrektor') => 'leadership',
        str_starts_with($title, 'koordynator') => 'coordinators',
        in_array($title, ['radca prawny', 'adwokat'], true) => 'lawyers',
        default => 'support',
    };
    $teamGroups[$group]['people'][] = $person;
}
?>
<section class="team-hero" aria-labelledby="team-title">
  <div class="team-frame team-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">Zespół</span>
      <h1 id="team-title">Ludzie stojący za sprawami.</h1>
    </div>
    <p data-reveal="subtle">Nasz zespół tworzą radcowie prawni, adwokaci i&nbsp;specjaliści wspierający codzienną pracę kancelarii. Łączymy doświadczenie i&nbsp;różne perspektywy w&nbsp;pracy nad sprawami klientów.</p>
  </div>
</section>

<div class="team-directory">
  <?php foreach ($teamGroups as $group => ['heading' => $heading, 'people' => $people]): ?>
    <?php if (!$people) continue; ?>
    <?php $headingId = 'team-' . $group . '-title'; ?>
    <section class="team-group team-group--<?= e($group) ?>" aria-labelledby="<?= e($headingId) ?>"<?= $group === 'leadership' ? ' data-home-surface="beige"' : '' ?>>
      <div class="team-frame team-group-layout">
        <h2 class="team-group-heading" id="<?= e($headingId) ?>" data-reveal="subtle"><?= e($heading) ?></h2>
        <ul class="team-list" role="list" data-reveal-sequence>
          <?php foreach ($people as $person): ?>
            <?php
            $portrait = $person['portrait'] ?? '';
            $hasPortrait = is_string($portrait)
                && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $portrait)
                && is_file(__DIR__ . '/../../public/assets/img/' . $portrait);
            ?>
            <li class="team-person<?= $hasPortrait ? ' has-portrait' : '' ?>" data-reveal="team-person">
              <?php if ($hasPortrait): ?>
                <img class="team-portrait" src="/assets/img/<?= e($portrait) ?>" alt="" loading="lazy" decoding="async">
              <?php endif; ?>
              <div>
                <h3><?= e($person['name']) ?></h3>
                <p><?= e($person['role']) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
  <?php endforeach; ?>
</div>

<section class="team-career" aria-labelledby="team-career-title">
  <div class="team-frame team-career-layout">
    <h2 id="team-career-title" data-reveal="subtle">Dołącz do naszego zespołu.</h2>
    <div data-reveal="subtle">
      <p>Sprawdź informacje o&nbsp;pracy i&nbsp;możliwościach współpracy z&nbsp;naszą kancelarią.</p>
      <a class="text-action team-career-action" href="/kariera/">
        <span>Zobacz karierę</span>
        <svg class="team-career-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
          <path d="M2 9h24m-7-7 7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>
