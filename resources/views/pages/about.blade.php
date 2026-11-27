@extends('layouts.public')

@section('content')
<section class="about-hero" aria-labelledby="about-title">
  <div class="about-frame about-hero-layout">
    <div class="about-hero-copy" data-reveal>
      <span class="section-kicker">O kancelarii</span>
      <h1 id="about-title">Kancelaria tworzona przez praktyków.</h1>
      <p>Łączymy doradztwo z&nbsp;doświadczeniem w&nbsp;prowadzeniu sporów.</p>
    </div>
    <svg class="about-brand-study" viewBox="0 0 110 76" aria-hidden="true" focusable="false" data-home-surface="beige" data-reveal>
      <defs>
        <path id="about-monogram" d="M 23 7.2 V 33 c 0 1 0 2 -0.2 3 c -0.5 4 -2.2 7.4 -5 9.9 c 0.9 -0.2 1.8 -0.6 2.7 -1 c 5.7 -2.5 8.8 -6.9 9.6 -13 l 0.1 -3 V 0 L 23 7.2 Z M 68.7 0 v 23 a 12.8 12.8 0 0 0 0 -23 Z M 45.9 7.2 v 38.6 l 7.2 -7.2 V 0 l -7.2 7.2 Z M 7.3 46 V 23 a 12.9 12.9 0 0 0 0 23 Z M 76 38.7 V 46 h -7.3 l 7.3 -7.3 Z M 0 7.3 V 0 h 7.3 L 0 7.3 Z" />
        <clipPath id="about-office-mask">
          <path d="M 23 7.2 V 33 c 0 1 0 2 -0.2 3 c -0.5 4 -2.2 7.4 -5 9.9 c 0.9 -0.2 1.8 -0.6 2.7 -1 c 5.7 -2.5 8.8 -6.9 9.6 -13 l 0.1 -3 V 0 L 23 7.2 Z M 68.7 0 v 23 a 12.8 12.8 0 0 0 0 -23 Z M 45.9 7.2 v 38.6 l 7.2 -7.2 V 0 l -7.2 7.2 Z"/>
        </clipPath>
      </defs>
      <path class="about-brand-guides" d="M 8 12 H 104 M 4 64 H 98 M 39 4 V 71 M 64 5 V 72 M 93 7 V 68 M 12 57 L 65 4 M 61 72 L 99 34"/>
      <g transform="translate(13 12) scale(1.13)">
        <use href="#about-monogram" class="about-brand-monogram"/>
        <image href="/assets/img/office-reception-800.webp" x="14" y="-2" width="67" height="51" preserveAspectRatio="xMidYMid slice" clip-path="url(#about-office-mask)"/>
      </g>
    </svg>
  </div>
</section>

<section class="about-identity" aria-labelledby="about-identity-title">
  <div class="about-frame">
    <div class="about-identity-copy">
      <div class="about-identity-statement" data-reveal>
        <h2 class="section-kicker" id="about-identity-title">Kim jesteśmy</h2>
        <p class="about-lead">Doradzamy firmom i&nbsp;klientom indywidualnym oraz reprezentujemy ich w&nbsp;sporach.</p>
      </div>
      <div class="about-identity-details" data-reveal>
        <p>W naszym wrocławskim zespole pracuje ponad dwadzieścia osób. Łączymy doświadczenie zdobyte w&nbsp;przedsiębiorstwach z&nbsp;praktyką doradczą i&nbsp;procesową. Wspieramy też instytucje, w&nbsp;tym podmioty działające na rynkach regulowanych.</p>
        <p>Nad sprawami pracują radcowie prawni i&nbsp;adwokaci przy wsparciu specjalistów prawnych i&nbsp;administracji. Łączymy wiedzę z&nbsp;różnych dziedzin prawa, kiedy jedna sprawa wymaga spojrzenia z&nbsp;kilku stron.</p>
      </div>
    </div>
  </div>
</section>

<section class="about-experience" aria-labelledby="about-experience-title">
  <div class="about-frame">
    <h2 class="about-section-title" id="about-experience-title" data-reveal>Nasze doświadczenie</h2>
    <ul class="about-experience-list" role="list">
      <li data-reveal>
        <h3>Biznes i&nbsp;przedsiębiorcy</h3>
        <div>
          <p>Pomagamy w&nbsp;bieżącej obsłudze firm i&nbsp;spółek. Przygotowujemy i&nbsp;negocjujemy umowy, doradzamy przy zmianach w&nbsp;spółkach i&nbsp;wspieramy przedsiębiorców w&nbsp;sporach gospodarczych.</p>
          <a class="text-action about-action" href="/specjalizacje/prawo-gospodarcze/"><span class="about-action-label">Prawo gospodarcze</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
        </div>
      </li>
      <li data-reveal>
        <h3>Bankowość i&nbsp;ubezpieczenia</h3>
        <div>
          <p>Doradzamy bankom, instytucjom finansowym, ubezpieczycielom i&nbsp;pośrednikom. Wspieramy ich w&nbsp;kwestiach regulacyjnych i&nbsp;reprezentujemy w&nbsp;sporach, między innymi dotyczących umów kredytowych i&nbsp;roszczeń ubezpieczeniowych.</p>
          <div class="about-experience-links">
            <a class="text-action about-action" href="/specjalizacje/bankowosc-finanse/"><span class="about-action-label">Bankowość i&nbsp;finanse</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
            <a class="text-action about-action" href="/specjalizacje/prawo-ubezpieczeniowe/"><span class="about-action-label">Prawo ubezpieczeniowe</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
          </div>
        </div>
      </li>
      <li data-reveal>
        <h3>Spory i&nbsp;reprezentacja</h3>
        <div>
          <p>Oceniamy ryzyko sporu, przygotowujemy strategię i&nbsp;reprezentujemy klientów w&nbsp;postępowaniach cywilnych, rodzinnych i&nbsp;administracyjnych. Gdy sprawa na to pozwala, pomagamy wypracować ugodę.</p>
          <a class="text-action about-action" href="/specjalizacje/postepowania-sadowe/"><span class="about-action-label">Postępowania sądowe</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
        </div>
      </li>
    </ul>
    <a class="text-action about-action about-experience-all" data-reveal href="/specjalizacje/"><span class="about-action-label">Zobacz wszystkie specjalizacje</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
  </div>
</section>

<div class="about-chapter" data-home-surface="beige">
<section class="about-method" aria-labelledby="about-method-title">
  <div class="about-frame about-method-layout">
    <h2 class="about-section-title" id="about-method-title" data-reveal>Jak pracujemy</h2>
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
    <h2 class="about-section-title" id="about-cooperation-title" data-reveal>Formy współpracy</h2>
    <div class="about-cooperation-options">
      <div data-reveal>
        <h3>Stała obsługa</h3>
        <p>Firmom i&nbsp;organizacjom zapewniamy bieżące wsparcie prawne w&nbsp;uzgodnionym zakresie. Wewnętrznym zespołom prawnym pomagamy także w&nbsp;sprawach wymagających specjalistycznej wiedzy.</p>
      </div>
      <div data-reveal>
        <h3>Wsparcie w&nbsp;konkretnej sprawie</h3>
        <p>Możesz zwrócić się do nas w&nbsp;sprawie pojedynczej porady, przygotowania umowy, negocjacji albo prowadzenia sporu. Zakres pomocy ustalamy przy danym zleceniu.</p>
      </div>
    </div>
  </div>
</section>

<section class="about-team" aria-labelledby="about-team-title">
  <div class="about-frame about-team-layout">
    <h2 class="about-section-title" id="about-team-title" data-reveal>Za każdą sprawą stoją ludzie.</h2>
    <div class="about-team-copy" data-reveal>
      <p>Nasz zespół tworzą radcowie prawni, adwokaci oraz osoby, które wspierają ich w&nbsp;pracy prawnej i&nbsp;organizacyjnej. Łączymy doświadczenie z&nbsp;różnych dziedzin prawa, gdy sprawa wymaga spojrzenia z&nbsp;kilku perspektyw.</p>
      <p>Poznaj osoby, które prowadzą sprawy i&nbsp;wspierają pracę kancelarii, oraz dowiedz się, jakie role pełnią w&nbsp;zespole.</p>
      <a class="text-action about-action" href="/zespol/"><span class="about-action-label">Poznaj nasz zespół</span>@include('partials.action-arrow', ['arrowClass' => 'about-action-arrow'])</a>
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
      @foreach ($site['documents'] as $doc)
        <li data-reveal>
          <a class="about-document" href="/dokumenty/{{ $doc['slug'] }}/">
            <span class="row-title">{{ $doc['title'] }}</span>
            <span class="row-summary">{{ $doc['summary'] }}</span>
            <span class="about-document-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endsection
