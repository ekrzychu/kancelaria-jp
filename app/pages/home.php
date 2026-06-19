<section class="hero page-reveal">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="section-kicker">Kancelaria Radców Prawnych · Wrocław</span>
      <h1>Prawo prowadzone spokojnie, precyzyjnie i skutecznie.</h1>
      <p>Wspieramy przedsiębiorców, instytucje i klientów indywidualnych w sprawach wymagających jasnej strategii, doświadczenia procesowego oraz odpowiedzialnej komunikacji.</p>
      <div class="hero-actions">
        <a class="button" href="/specjalizacje/">Zobacz specjalizacje</a>
        <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię <?= icon('arrow', 'inline-arrow') ?></a>
      </div>
    </div>
    <figure class="hero-photo">
      <picture>
        <source srcset="/assets/img/office-reception-hero.webp" type="image/webp">
        <img src="/assets/img/office-reception-hero.jpg" alt="Recepcja kancelarii z logo JP" loading="eager">
      </picture>
    </figure>
  </div>
</section>

<section class="section intro-section page-reveal">
  <div class="container editorial-split">
    <div>
      <span class="section-kicker">O kancelarii</span>
      <h2>Szerokie kompetencje, skuteczność w działaniu.</h2>
    </div>
    <div class="rich-text">
      <p>Kancelaria łączy doświadczenie zdobyte przy obsłudze firm, rynku finansowego, spraw rodzinnych, spadkowych i sporów sądowych. Działamy zespołowo, ale komunikujemy się prosto — tak, aby klient wiedział, jaki jest cel, zakres działań i kolejny krok.</p>
      <p>Współpraca może mieć charakter stałej obsługi prawnej albo wsparcia w konkretnej sprawie. W każdym przypadku punkt wyjścia jest ten sam: uważna analiza problemu i dobór właściwego rozwiązania.</p>
    </div>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container">
    <div class="section-heading">
      <span class="section-kicker">Zakres praktyk</span>
      <h2>Najważniejsze obszary wsparcia.</h2>
    </div>
    <div class="practice-list">
      <?php foreach (array_slice($services, 0, 8) as $i => $service): ?>
        <a class="practice-row" href="/specjalizacje/<?= e($service['slug']) ?>/">
          <span class="row-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="row-main">
            <strong><?= e($service['title']) ?></strong>
            <em><?= e($service['summary']) ?></em>
          </span>
          <span class="row-link">Czytaj</span>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="section-end"><a class="text-action" href="/specjalizacje/">Wszystkie specjalizacje <?= icon('arrow', 'inline-arrow') ?></a></p>
  </div>
</section>

<section class="image-statement page-reveal">
  <picture>
    <source srcset="/assets/img/building-exterior-wide.webp" type="image/webp">
    <img src="/assets/img/building-exterior-wide.jpg" alt="Budynek przy Placu Tadeusza Kościuszki we Wrocławiu" loading="lazy">
  </picture>
  <div class="container image-statement-panel">
    <span class="section-kicker">Wrocław</span>
    <h2>Stała obecność w centrum miasta, blisko spraw klientów.</h2>
  </div>
</section>

<section class="section page-reveal">
  <div class="container standards-grid">
    <div>
      <span class="section-kicker">Standard pracy</span>
      <h2>Formalnie tam, gdzie trzeba. Praktycznie tam, gdzie to możliwe.</h2>
    </div>
    <div class="standards-list">
      <div>
        <strong>Analiza</strong>
        <p>Na początku porządkujemy fakty, dokumenty i cel sprawy.</p>
      </div>
      <div>
        <strong>Strategia</strong>
        <p>Dobieramy środki działania do ryzyka, czasu i oczekiwanego rezultatu.</p>
      </div>
      <div>
        <strong>Komunikacja</strong>
        <p>Informujemy jasno o kolejnych etapach i decyzjach wymagających udziału klienta.</p>
      </div>
    </div>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container editorial-split">
    <div>
      <span class="section-kicker">Publikacje</span>
      <h2>Artykuły i komentarze prawne.</h2>
    </div>
    <div class="rich-text">
      <p>W finalnym wdrożeniu ta część może prezentować aktualne analizy i poradniki. Na etapie koncepcyjnym zostawiamy czystą stronę artykułów bez przenoszenia całego archiwum.</p>
      <p><a class="button secondary" href="/artykuly/">Przejdź do artykułów</a></p>
    </div>
  </div>
</section>
