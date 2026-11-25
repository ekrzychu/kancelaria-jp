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
    <svg class="about-relief" viewBox="82 24 504 338" aria-hidden="true" focusable="false">
      <defs>
        <clipPath id="about-relief-opening">
          <path d="M172 337V223l67-72 48-4c65-5 110 35 110 97v75Z"/>
        </clipPath>
      </defs>
      <path class="about-relief-top" d="M110 121 211 70 554 42 453 93Z"/>
      <path class="about-relief-side" d="M453 93 554 42V263l-101 51Z"/>
      <g clip-path="url(#about-relief-opening)">
        <path class="about-relief-reveal" d="M172 337V223l67-72 48-4 39-20-48 4-67 72v114Z"/>
        <path class="about-relief-inlay" d="M207 319V205l67-72 9-1-68 73v110Z"/>
        <path class="about-relief-edge" d="M211 317V203l67-72 48-4c65-5 110 35 110 97"/>
      </g>
      <path class="about-relief-face" d="M110 121 453 93V314l-56 5v-75c0-62-45-102-110-97l-48 4-67 72v114l-62 5Z"/>
      <path class="about-relief-edge" d="M126 133 438 108M467 103l72-36"/>
    </svg>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame">
    <div class="about-identity-copy" data-reveal>
      <div class="about-identity-statement">
        <h2 class="section-kicker" id="about-identity-title">Kim jesteśmy</h2>
        <p class="about-lead">Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      </div>
      <div class="about-identity-details">
        <p>W naszym wrocławskim zespole pracuje ponad dwadzieścia osób. Łączymy doświadczenie zdobyte w&nbsp;przedsiębiorstwach z&nbsp;praktyką doradczą i&nbsp;procesową. Wspieramy też instytucje, w&nbsp;tym podmioty działające na rynkach regulowanych.</p>
        <p>Nad sprawami pracują radcowie prawni i&nbsp;adwokaci przy wsparciu specjalistów prawnych i&nbsp;administracji. Łączymy wiedzę z&nbsp;różnych dziedzin prawa, kiedy jedna sprawa wymaga spojrzenia z&nbsp;kilku stron.</p>
      </div>
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

<div class="about-chapter" data-home-surface="beige">
<section class="about-method" aria-labelledby="about-method-title">
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

<section class="about-team" aria-labelledby="about-team-title">
  <div class="about-frame about-team-layout">
    <h2 class="about-section-title" id="about-team-title">Za każdą sprawą stoją ludzie.</h2>
    <div class="about-team-copy">
      <p>Zobacz, kto tworzy kancelarię i&nbsp;jakie role pełnią poszczególne osoby.</p>
      <a class="text-action about-action" href="/zespol/"><span class="about-action-label">Poznaj nasz zespół</span><?= $aboutArrow ?></a>
    </div>
  </div>
</section>
</div>

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
