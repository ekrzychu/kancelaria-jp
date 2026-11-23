<section class="home-hero">
  <div class="home-hero-grid">
    <div class="home-hero-copy">
      <p class="section-kicker">Kancelaria Radców Prawnych · Wrocław</p>
      <h1>Prawo prowadzone spokojnie, precyzyjnie <span>i&nbsp;skutecznie.</span></h1>
      <p class="home-hero-lead">Wspieramy przedsiębiorców, instytucje i&nbsp;klientów indywidualnych w&nbsp;sprawach wymagających jasnej strategii, doświadczenia procesowego oraz odpowiedzialnej komunikacji.</p>
      <a class="button" href="/specjalizacje/">Zobacz specjalizacje</a>
    </div>
    <figure class="home-hero-photo">
      <picture>
        <source media="(max-width: 42rem)" type="image/webp" srcset="/assets/img/office-reception-mobile.webp" width="840" height="560">
        <source type="image/webp" srcset="/assets/img/office-reception-800.webp 800w, /assets/img/office-reception-hero.webp 1150w" sizes="49vw">
        <img src="/assets/img/office-reception-hero.jpg" alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1150" height="1480" fetchpriority="high">
      </picture>
      <figcaption>Recepcja kancelarii we Wrocławiu</figcaption>
    </figure>
  </div>
</section>

<section class="home-section home-about">
  <div class="container editorial-split">
    <div data-reveal>
      <p class="section-kicker">O kancelarii</p>
      <h2>Szerokie kompetencje, skuteczność w&nbsp;działaniu.</h2>
    </div>
    <div class="rich-text">
      <p>Kancelaria łączy doświadczenie zdobyte przy obsłudze firm, rynku finansowego, spraw rodzinnych, spadkowych i sporów sądowych. Działamy zespołowo, ale komunikujemy się prosto — tak, aby klient wiedział, jaki jest cel, zakres działań i kolejny krok.</p>
      <p>Współpraca może mieć charakter stałej obsługi prawnej albo wsparcia w konkretnej sprawie. W każdym przypadku punkt wyjścia jest ten sam: uważna analiza problemu i dobór właściwego rozwiązania.</p>
      <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię</a>
    </div>
  </div>
</section>

<section class="home-section home-practices">
  <div class="container home-practices-grid">
    <div class="home-practices-intro" data-reveal>
      <p class="section-kicker">Specjalizacje</p>
      <h2>Najważniejsze obszary wsparcia.</h2>
      <a class="text-action" href="/specjalizacje/">Wszystkie specjalizacje</a>
    </div>
    <ul class="home-practice-list">
      <?php foreach (array_slice($services, 0, 8) as $service): ?>
        <li>
          <a class="home-practice-link" href="/specjalizacje/<?= e($service['slug']) ?>/">
            <h3><?= e($service['title']) ?></h3>
            <p><?= e($service['summary']) ?></p>
            <span class="practice-indicator" aria-hidden="true"><?= icon('arrow', 'inline-arrow') ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="home-section home-location">
  <figure class="home-location-photo" data-reveal>
    <picture>
      <source type="image/webp" srcset="/assets/img/building-exterior-800.webp 800w, /assets/img/building-exterior-wide.webp 1800w" sizes="(min-width: 91rem) 91rem, 100vw">
      <img src="/assets/img/building-exterior-wide.jpg" alt="Kamienna fasada budynku kancelarii przy placu Kościuszki we Wrocławiu" width="1800" height="980" loading="lazy" decoding="async">
    </picture>
  </figure>
  <div class="container home-location-grid">
    <div>
      <p class="section-kicker">Siedziba kancelarii</p>
      <h2>Stała obecność w&nbsp;centrum miasta, blisko spraw klientów.</h2>
    </div>
    <div>
      <address class="home-address"><?= implode('<br>', array_map('e', $site['address'])) ?></address>
      <a class="text-action" href="/kontakt/">Zobacz dane kontaktowe</a>
    </div>
  </div>
</section>

<section class="home-section home-standards">
  <div class="container">
    <div class="home-standards-heading" data-reveal>
      <p class="section-kicker">Standard pracy</p>
      <h2>Formalnie tam, gdzie trzeba.<br>Praktycznie tam, gdzie to&nbsp;możliwe.</h2>
    </div>
    <dl class="home-standards-list">
      <div>
        <dt>Analiza</dt>
        <dd>Na początku porządkujemy fakty, dokumenty i cel sprawy.</dd>
      </div>
      <div>
        <dt>Strategia</dt>
        <dd>Dobieramy środki działania do ryzyka, czasu i oczekiwanego rezultatu.</dd>
      </div>
      <div>
        <dt>Komunikacja</dt>
        <dd>Informujemy jasno o kolejnych etapach i decyzjach wymagających udziału klienta.</dd>
      </div>
    </dl>
  </div>
</section>

<section class="home-section home-publications">
  <div class="container home-publications-grid">
    <div>
      <p class="section-kicker">Publikacje</p>
      <h2>Artykuły i&nbsp;komentarze prawne.</h2>
    </div>
    <div>
      <p class="home-publications-lead">Publikujemy analizy, komentarze i praktyczne omówienia zmian w prawie. Archiwum jest obecnie aktualizowane.</p>
      <a class="button secondary" href="/artykuly/">Przejdź do artykułów</a>
    </div>
  </div>
</section>
