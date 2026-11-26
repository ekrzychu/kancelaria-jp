<?php
$recruitment = data_file('recruitment');
$vacancies = array_filter($recruitment['vacancies'], static fn (array $vacancy): bool => ($vacancy['status'] ?? '') === 'open');
$stages = [
    'Aplikacja' => 'Wyślij zgłoszenie zgodnie z wymaganiami danego naboru.',
    'Pierwsza rozmowa' => 'Wybrane osoby zapraszamy na spotkanie z koordynatorem zespołu, dyrektor zarządzającą lub z obojgiem.',
    'Dodatkowa rozmowa' => 'Po pierwszym spotkaniu możemy zaprosić Cię na rozmowę ze wspólnikami kancelarii.',
    'Decyzja' => 'Informację o wyniku rekrutacji przekazujemy mailowo lub telefonicznie.',
];
$careerArrow = <<<'SVG'
<svg class="career-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
  <path d="M2 9h24m-7-7 7 7-7 7"/>
</svg>
SVG;
?>
<section class="career-hero" aria-labelledby="career-title">
  <div class="career-frame career-split">
    <div data-reveal="subtle">
      <span class="section-kicker">Kariera</span>
      <h1 id="career-title">Rozwijaj swoją karierę razem z&nbsp;nami.</h1>
    </div>
    <p class="career-intro" data-reveal="subtle">Jeśli chcesz rozwijać swój warsztat prawniczy i&nbsp;pracować nad sprawami wspólnie z&nbsp;innymi, dowiedz się, jak wygląda rekrutacja.</p>
  </div>
</section>

<section class="career-opportunities" aria-labelledby="career-opportunities-title">
  <div class="career-frame">
    <h2 id="career-opportunities-title" data-reveal="subtle">Aktualne oferty pracy</h2>
    <?php if ($vacancies): ?>
      <ul class="editorial-list career-openings" role="list">
        <?php foreach ($vacancies as $vacancy): ?>
          <li class="editorial-row career-opening" data-reveal="subtle">
            <h3><?= e($vacancy['title']) ?></h3>
            <?php if (!empty($vacancy['description']) || !empty($vacancy['url'])): ?>
              <div>
                <?php if (!empty($vacancy['description'])): ?>
                  <p><?= e($vacancy['description']) ?></p>
                <?php endif; ?>
                <?php if (!empty($vacancy['url'])): ?>
                  <a class="text-action career-action" href="<?= e($vacancy['url']) ?>" aria-label="<?= e('Szczegóły ogłoszenia — ' . $vacancy['title']) ?>"><span>Szczegóły ogłoszenia</span><?= $careerArrow ?></a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="editorial-row career-opening" data-reveal="subtle">
        <h3>Brak opublikowanych ofert</h3>
        <div>
          <p>Jeśli interesuje Cię praca lub współpraca z&nbsp;kancelarią, możesz zapytać o&nbsp;możliwości zgłoszenia.</p>
          <a class="text-action career-action" href="#kontakt-rekrutacja"><span>Zapytaj o&nbsp;nabór</span><?= $careerArrow ?></a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="career-process" aria-labelledby="career-process-title" data-home-surface="beige">
  <div class="career-frame career-process-layout">
    <h2 id="career-process-title" data-reveal="subtle">Jak wygląda rekrutacja</h2>
    <ol class="career-steps" role="list" data-career-process>
      <?php $number = 0; ?>
      <?php foreach ($stages as $title => $description): ?>
        <li class="career-step">
          <span class="career-step-marker" aria-hidden="true"></span>
          <div class="career-step-copy">
            <span class="career-step-index" aria-hidden="true"><?= sprintf('%02d', ++$number) ?></span>
            <h3><?= e($title) ?></h3>
            <p><?= e($description) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="career-application" id="kontakt-rekrutacja" aria-labelledby="career-application-title" tabindex="-1">
  <div class="career-frame career-split">
    <div data-reveal="subtle">
      <span class="section-kicker">Kontakt w sprawie rekrutacji</span>
      <h2 id="career-application-title">Chcesz do nas dołączyć?</h2>
    </div>
    <div class="career-application-copy" data-reveal="subtle">
      <p>Przed wysłaniem zgłoszenia zapytaj o&nbsp;aktualny adres rekrutacyjny, wymagane dokumenty oraz informacje o&nbsp;przetwarzaniu danych kandydatów.</p>
      <a class="text-action career-action" href="mailto:<?= e($site['email']) ?>?subject=Rekrutacja"><span><?= e($site['email']) ?></span><?= $careerArrow ?></a>
    </div>
  </div>
</section>
