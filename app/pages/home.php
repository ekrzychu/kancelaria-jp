<section class="home-hero">
  <div class="container home-hero-grid">
    <div>
      <p class="section-kicker">Kancelaria Radców Prawnych · Wrocław</p>
      <h1>Prawo prowadzone spokojnie, precyzyjnie i&nbsp;skutecznie.</h1>
      <p class="home-hero-lead">Wspieramy przedsiębiorców, instytucje i&nbsp;klientów indywidualnych w&nbsp;sprawach wymagających jasnej strategii, doświadczenia procesowego oraz odpowiedzialnej komunikacji.</p>
      <div class="home-hero-actions">
        <a class="button" href="/specjalizacje/">Zobacz specjalizacje</a>
        <a class="text-action" href="/o-kancelarii/">Poznaj kancelarię</a>
      </div>
    </div>
    <figure class="home-hero-photo">
      <picture>
        <source media="(max-width: 42rem)" srcset="/assets/img/office-reception-wide.webp" type="image/webp" width="1800" height="900">
        <source srcset="/assets/img/office-reception-hero.webp" type="image/webp">
        <img src="/assets/img/office-reception-hero.jpg" alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1150" height="1480" fetchpriority="high">
      </picture>
      <figcaption>Recepcja kancelarii we Wrocławiu</figcaption>
    </figure>
  </div>
</section>

<section class="home-section home-about">
  <div class="container home-about-grid">
    <div>
      <p class="section-kicker">O kancelarii</p>
      <h2>Szerokie kompetencje, skuteczność w&nbsp;działaniu.</h2>
    </div>
    <div class="rich-text">
      <p>Kancelaria łączy doświadczenie zdobyte przy obsłudze firm, rynku finansowego, spraw rodzinnych, spadkowych i sporów sądowych. Działamy zespołowo, ale komunikujemy się prosto — tak, aby klient wiedział, jaki jest cel, zakres działań i kolejny krok.</p>
      <p>Współpraca może mieć charakter stałej obsługi prawnej albo wsparcia w konkretnej sprawie. W każdym przypadku punkt wyjścia jest ten sam: uważna analiza problemu i dobór właściwego rozwiązania.</p>
    </div>
  </div>
</section>

<section class="home-section">
  <div class="container">
    <div class="home-section-heading">
      <div>
        <p class="section-kicker">Specjalizacje</p>
        <h2>Najważniejsze obszary wsparcia.</h2>
      </div>
      <a class="text-action" href="/specjalizacje/">Wszystkie specjalizacje</a>
    </div>
    <ul class="home-practice-list">
      <?php foreach (array_slice($services, 0, 8) as $service): ?>
        <li>
          <a class="home-practice-link" href="/specjalizacje/<?= e($service['slug']) ?>/">
            <h3><?= e($service['title']) ?></h3>
            <p><?= e($service['summary']) ?></p>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="home-section home-location">
  <div class="container home-location-grid">
    <picture>
      <source srcset="/assets/img/building-exterior-wide.webp" type="image/webp">
      <img src="/assets/img/building-exterior-wide.jpg" alt="Kamienna fasada budynku kancelarii przy placu Kościuszki we Wrocławiu" width="1800" height="980" loading="lazy">
    </picture>
    <div>
      <p class="section-kicker">Siedziba kancelarii</p>
      <h2>Stała obecność w&nbsp;centrum miasta, blisko spraw klientów.</h2>
      <p class="home-address"><?= implode('<br>', array_map('e', $site['address'])) ?></p>
      <a class="text-action" href="/kontakt/">Zobacz dane kontaktowe</a>
    </div>
  </div>
</section>

<section class="home-section">
  <div class="container home-standards-inner">
    <div>
      <p class="section-kicker">Standard pracy</p>
      <h2>Formalnie tam, gdzie trzeba.<br> Praktycznie tam, gdzie to&nbsp;możliwe.</h2>
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
  <div class="container home-section-heading">
    <div>
      <p class="section-kicker">Publikacje</p>
      <h2>Artykuły i&nbsp;komentarze prawne.</h2>
      <p class="home-publications-lead">Publikujemy analizy, komentarze i praktyczne omówienia zmian w prawie. Archiwum jest obecnie aktualizowane.</p>
    </div>
    <a class="text-action" href="/artykuly/">Przejdź do artykułów</a>
  </div>
</section>
