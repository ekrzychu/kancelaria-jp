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
    <div class="about-hero-copy" data-reveal="subtle">
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title">Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <!-- The upright and bowl are cropped from the original favicon.svg artwork. -->
    <svg class="about-brand-study" viewBox="249 127 232 156" aria-hidden="true" focusable="false" data-home-surface="beige" data-reveal="subtle">
      <path class="about-brand-upright" d="M303.072510,171.082901   C312.249176,161.881592 321.175110,152.931519 330.727448,143.353348   C330.878479,145.510651 331.058685,146.886108 331.058990,148.261597   C331.072968,208.904800 331.106873,269.548096 330.940643,330.190857   C330.933960,332.629242 329.602264,335.630127 327.889130,337.404755   C317.633057,348.028992 307.083893,358.370270 295.702301,369.735779   C295.702301,366.736053 295.702301,364.840576 295.702301,362.945129   C295.702301,304.800964 295.836212,246.656082 295.532349,188.513489   C295.494507,181.265884 296.847046,175.567673 303.072510,171.082901  z"/>
      <path class="about-brand-bowl" d="M441.623291,216.595001   C436.216736,234.709854 425.455170,247.847885 408.548950,256.869171   C408.548950,218.719864 408.548950,181.368271 408.548950,144.007675   C431.627747,148.829529 448.070221,184.606934 441.623291,216.595001  z"/>
    </svg>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame">
    <div class="about-identity-copy">
      <div class="about-identity-statement" data-reveal="subtle">
        <h2 class="section-kicker" id="about-identity-title">Kim jesteśmy</h2>
        <p class="about-lead">Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      </div>
      <div class="about-identity-details" data-reveal="subtle">
        <p>W naszym wrocławskim zespole pracuje ponad dwadzieścia osób. Łączymy doświadczenie zdobyte w&nbsp;przedsiębiorstwach z&nbsp;praktyką doradczą i&nbsp;procesową. Wspieramy też instytucje, w&nbsp;tym podmioty działające na rynkach regulowanych.</p>
        <p>Nad sprawami pracują radcowie prawni i&nbsp;adwokaci przy wsparciu specjalistów prawnych i&nbsp;administracji. Łączymy wiedzę z&nbsp;różnych dziedzin prawa, kiedy jedna sprawa wymaga spojrzenia z&nbsp;kilku stron.</p>
      </div>
    </div>
  </div>
</section>

<section class="about-experience" aria-labelledby="about-experience-title">
  <div class="about-frame">
    <h2 class="about-section-title" id="about-experience-title" data-reveal="subtle">Nasze doświadczenie</h2>
    <ul class="about-experience-list" role="list">
      <li data-reveal="subtle">
        <h3>Biznes i&nbsp;przedsiębiorcy</h3>
        <div>
          <p>Pomagamy w&nbsp;bieżącej obsłudze firm i&nbsp;spółek. Przygotowujemy i&nbsp;negocjujemy umowy, doradzamy przy zmianach w&nbsp;spółkach i&nbsp;wspieramy przedsiębiorców w&nbsp;sporach gospodarczych.</p>
          <a class="text-action about-action" href="/specjalizacje/prawo-gospodarcze/"><span class="about-action-label">Prawo gospodarcze</span><?= $aboutArrow ?></a>
        </div>
      </li>
      <li data-reveal="subtle">
        <h3>Bankowość i&nbsp;ubezpieczenia</h3>
        <div>
          <p>Doradzamy bankom, instytucjom finansowym, ubezpieczycielom i&nbsp;pośrednikom. Wspieramy ich w&nbsp;kwestiach regulacyjnych i&nbsp;reprezentujemy w&nbsp;sporach, między innymi dotyczących umów kredytowych i&nbsp;roszczeń ubezpieczeniowych.</p>
          <div class="about-experience-links">
            <a class="text-action about-action" href="/specjalizacje/bankowosc-finanse/"><span class="about-action-label">Bankowość i&nbsp;finanse</span><?= $aboutArrow ?></a>
            <a class="text-action about-action" href="/specjalizacje/prawo-ubezpieczeniowe/"><span class="about-action-label">Prawo ubezpieczeniowe</span><?= $aboutArrow ?></a>
          </div>
        </div>
      </li>
      <li data-reveal="subtle">
        <h3>Spory i&nbsp;reprezentacja</h3>
        <div>
          <p>Oceniamy ryzyko sporu, przygotowujemy strategię i&nbsp;reprezentujemy klientów w&nbsp;postępowaniach cywilnych, rodzinnych i&nbsp;administracyjnych. Gdy sprawa na to pozwala, pomagamy wypracować ugodę.</p>
          <a class="text-action about-action" href="/specjalizacje/postepowania-sadowe/"><span class="about-action-label">Postępowania sądowe</span><?= $aboutArrow ?></a>
        </div>
      </li>
    </ul>
    <a class="text-action about-action about-experience-all" data-reveal="subtle" href="/specjalizacje/"><span class="about-action-label">Zobacz wszystkie specjalizacje</span><?= $aboutArrow ?></a>
  </div>
</section>

<div class="about-chapter" data-home-surface="beige">
<section class="about-method" aria-labelledby="about-method-title">
  <div class="about-frame about-method-layout">
    <h2 class="about-section-title" id="about-method-title" data-reveal="subtle">Jak pracujemy</h2>
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
    <h2 class="about-section-title" id="about-cooperation-title" data-reveal="subtle">Formy współpracy</h2>
    <div class="about-cooperation-options">
      <div data-reveal="subtle">
        <h3>Stała obsługa</h3>
        <p>Firmom i&nbsp;organizacjom zapewniamy bieżące wsparcie prawne w&nbsp;uzgodnionym zakresie. Wewnętrznym zespołom prawnym pomagamy także w&nbsp;sprawach wymagających specjalistycznej wiedzy.</p>
      </div>
      <div data-reveal="subtle">
        <h3>Wsparcie w&nbsp;konkretnej sprawie</h3>
        <p>Możesz zwrócić się do nas w&nbsp;sprawie pojedynczej porady, przygotowania umowy, negocjacji albo prowadzenia sporu. Zakres pomocy ustalamy przy danym zleceniu.</p>
      </div>
    </div>
  </div>
</section>

<section class="about-team" aria-labelledby="about-team-title">
  <div class="about-frame about-team-layout">
    <h2 class="about-section-title" id="about-team-title" data-reveal="subtle">Za każdą sprawą stoją ludzie.</h2>
    <div class="about-team-copy" data-reveal="subtle">
      <p>Nasz zespół tworzą radcowie prawni, adwokaci oraz osoby, które wspierają ich w&nbsp;pracy prawnej i&nbsp;organizacyjnej. Łączymy doświadczenie z&nbsp;różnych dziedzin prawa, gdy sprawa wymaga spojrzenia z&nbsp;kilku perspektyw.</p>
      <p>Poznaj osoby, które prowadzą sprawy i&nbsp;wspierają pracę kancelarii, oraz dowiedz się, jakie role pełnią w&nbsp;zespole.</p>
      <a class="text-action about-action" href="/zespol/"><span class="about-action-label">Poznaj nasz zespół</span><?= $aboutArrow ?></a>
    </div>
  </div>
</section>
</div>

<section class="about-documents" aria-labelledby="about-documents-title">
  <div class="about-frame">
    <div class="about-documents-intro" data-reveal="subtle">
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
