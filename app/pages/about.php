<?php
$aboutArrow = <<<'SVG'
<svg class="about-action-arrow" viewBox="0 0 28 18" width="28" height="18"
     fill="none" stroke="currentColor" stroke-width="1.6"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
  <path d="M2 9h24m-7-7 7 7-7 7"/>
</svg>
SVG;
?>
<section class="about-hero" aria-labelledby="about-title">
  <div class="about-frame about-hero-layout">
    <div class="about-hero-copy" data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title">Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <svg class="about-fold" viewBox="0 0 640 358" aria-hidden="true" focusable="false">
      <path class="about-fold-edge" d="M70 301C174 271 174 136 271 114C354 95 397 217 478 200C523 190 546 142 581 105v18C546 160 523 208 478 218C397 235 354 113 271 132C174 154 174 289 70 319Z"/>
      <path class="about-fold-face" d="M70 241C148 194 149 75 259 63C353 53 394 160 467 140C512 128 529 90 581 55v50C546 142 523 190 478 200C397 217 354 95 271 114C174 136 174 271 70 301Z"/>
      <path class="about-fold-rulings" d="M70 248C151 203 152 82 260 69C353 58 394 166 468 147C513 135 531 96 581 61
        M70 254C154 211 155 89 262 74C353 62 395 173 469 153C514 142 533 102 581 66
        M70 261C157 220 157 95 263 80C353 67 395 179 471 160C516 149 535 107 581 72
        M70 268C160 228 160 102 264 86C353 72 395 185 472 167C517 156 537 113 581 77
        M70 274C162 237 163 109 266 91C354 76 396 192 473 173C518 162 538 119 581 83
        M70 281C165 245 166 116 267 97C354 81 396 198 474 180C519 169 540 125 581 88
        M70 288C168 254 168 122 268 103C354 86 396 204 476 187C521 176 542 130 581 94
        M70 294C171 262 171 129 270 108C354 90 397 211 477 193C522 183 544 136 581 99"/>
    </svg>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame">
    <div class="about-identity-copy" data-reveal>
      <h2 class="section-kicker" id="about-identity-title">Kim jesteśmy</h2>
      <p class="about-lead">Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      <p class="about-identity-detail">W naszym wrocławskim zespole pracuje ponad dwadzieścia osób. Łączymy doświadczenie zdobyte w&nbsp;przedsiębiorstwach z&nbsp;praktyką doradczą i&nbsp;procesową. Wspieramy też instytucje, w&nbsp;tym podmioty działające na rynkach regulowanych.</p>
      <p class="about-identity-detail">Nad sprawami pracują radcowie prawni i&nbsp;adwokaci przy wsparciu specjalistów prawnych i&nbsp;administracji. Łączymy wiedzę z&nbsp;różnych dziedzin prawa, kiedy jedna sprawa wymaga spojrzenia z&nbsp;kilku stron.</p>
    </div>
  </div>
</section>

<section class="about-experience" aria-labelledby="about-experience-title">
  <div class="about-frame">
    <h2 class="about-section-title" id="about-experience-title">Nasze doświadczenie</h2>
    <ul class="about-experience-list" role="list">
      <li>
        <h3>Biznes i&nbsp;przedsiębiorcy</h3>
        <div>
          <p>Pomagamy w&nbsp;bieżącej obsłudze firm i&nbsp;spółek. Przygotowujemy i&nbsp;negocjujemy umowy, doradzamy przy zmianach w&nbsp;spółkach i&nbsp;wspieramy przedsiębiorców w&nbsp;sporach gospodarczych.</p>
          <a class="text-action about-action" href="/specjalizacje/prawo-gospodarcze/"><span class="about-action-label">Prawo gospodarcze</span><?= $aboutArrow ?></a>
        </div>
      </li>
      <li>
        <h3>Bankowość i&nbsp;ubezpieczenia</h3>
        <div>
          <p>Doradzamy bankom, instytucjom finansowym, ubezpieczycielom i&nbsp;pośrednikom. Wspieramy ich w&nbsp;kwestiach regulacyjnych i&nbsp;reprezentujemy w&nbsp;sporach, między innymi dotyczących umów kredytowych i&nbsp;roszczeń ubezpieczeniowych.</p>
          <div class="about-experience-links">
            <a class="text-action about-action" href="/specjalizacje/bankowosc-finanse/"><span class="about-action-label">Bankowość i&nbsp;finanse</span><?= $aboutArrow ?></a>
            <a class="text-action about-action" href="/specjalizacje/prawo-ubezpieczeniowe/"><span class="about-action-label">Prawo ubezpieczeniowe</span><?= $aboutArrow ?></a>
          </div>
        </div>
      </li>
      <li>
        <h3>Spory i&nbsp;reprezentacja</h3>
        <div>
          <p>Oceniamy ryzyko sporu, przygotowujemy strategię i&nbsp;reprezentujemy klientów w&nbsp;postępowaniach cywilnych, rodzinnych i&nbsp;administracyjnych. Gdy sprawa na to pozwala, pomagamy wypracować ugodę.</p>
          <a class="text-action about-action" href="/specjalizacje/postepowania-sadowe/"><span class="about-action-label">Postępowania sądowe</span><?= $aboutArrow ?></a>
        </div>
      </li>
    </ul>
    <a class="text-action about-action about-experience-all" href="/specjalizacje/"><span class="about-action-label">Zobacz wszystkie specjalizacje</span><?= $aboutArrow ?></a>
  </div>
</section>

<section class="about-method" aria-labelledby="about-method-title" data-home-surface="beige">
  <div class="about-frame about-method-layout">
    <h2 class="about-section-title" id="about-method-title">Jak pracujemy</h2>
    <ol class="about-steps" role="list" data-about-process>
      <li class="about-step">
        <span class="about-step-marker" aria-hidden="true"></span>
        <div class="about-step-copy">
          <span class="about-step-index" aria-hidden="true">01</span>
          <h3>Rozpoznanie sprawy</h3>
          <p>Poznajemy okoliczności sprawy, przeglądamy dokumenty i&nbsp;ustalamy z&nbsp;klientem, co chce osiągnąć.</p>
        </div>
      </li>
      <li class="about-step">
        <span class="about-step-marker" aria-hidden="true"></span>
        <div class="about-step-copy">
          <span class="about-step-index" aria-hidden="true">02</span>
          <h3>Dobór rozwiązania</h3>
          <p>Omawiamy możliwe kierunki działania i&nbsp;ryzyka. Rekomendujemy rozwiązanie, biorąc pod uwagę cele klienta i&nbsp;charakter sprawy.</p>
        </div>
      </li>
      <li class="about-step">
        <span class="about-step-marker" aria-hidden="true"></span>
        <div class="about-step-copy">
          <span class="about-step-index" aria-hidden="true">03</span>
          <h3>Prowadzenie</h3>
          <p>Realizujemy uzgodnioną strategię, śledzimy przebieg sprawy i&nbsp;informujemy klienta o&nbsp;postępach oraz kolejnych decyzjach.</p>
        </div>
      </li>
    </ol>
  </div>
</section>

<section class="about-cooperation" aria-labelledby="about-cooperation-title">
  <div class="about-frame">
    <h2 class="about-section-title" id="about-cooperation-title">Formy współpracy</h2>
    <div class="about-cooperation-options">
      <div>
        <h3>Stała obsługa</h3>
        <p>Firmom i&nbsp;organizacjom zapewniamy bieżące wsparcie prawne w&nbsp;uzgodnionym zakresie. Wewnętrznym zespołom prawnym pomagamy także w&nbsp;sprawach wymagających specjalistycznej wiedzy.</p>
      </div>
      <div>
        <h3>Wsparcie w&nbsp;konkretnej sprawie</h3>
        <p>Możesz zwrócić się do nas w&nbsp;sprawie pojedynczej porady, przygotowania umowy, negocjacji albo prowadzenia sporu. Zakres pomocy ustalamy przy danym zleceniu.</p>
      </div>
    </div>
  </div>
</section>

<section class="about-team" aria-labelledby="about-team-title" data-home-surface="beige">
  <div class="about-frame about-team-layout">
    <h2 class="about-section-title" id="about-team-title">Za każdą sprawą stoją ludzie.</h2>
    <div>
      <p>Zobacz, kto tworzy kancelarię i&nbsp;jakie role pełnią poszczególne osoby.</p>
      <a class="text-action about-action" href="/zespol/"><span class="about-action-label">Poznaj nasz zespół</span><?= $aboutArrow ?></a>
    </div>
  </div>
</section>

<section class="about-documents" aria-labelledby="about-documents-title">
  <div class="about-frame">
    <div class="about-documents-intro" data-reveal>
      <span class="section-kicker">Dokumenty</span>
      <h2 class="about-section-title" id="about-documents-title">Standardy i&nbsp;informacje formalne.</h2>
    </div>
    <ul class="about-document-list" role="list" data-reveal-sequence>
      <?php foreach ($site['documents'] as $doc): ?>
        <li data-reveal>
          <a class="about-document" href="/dokumenty/<?= e($doc['slug']) ?>/">
            <span class="row-title"><?= e($doc['title']) ?></span>
            <span class="row-summary"><?= e($doc['summary']) ?></span>
            <span class="about-document-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
