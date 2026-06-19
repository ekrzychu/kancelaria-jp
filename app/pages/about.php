<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">O kancelarii</span>
    <h1>Kancelaria tworzona przez praktyków.</h1>
    <p>Obsługa prawna oparta na wiedzy, doświadczeniu i odpowiedzialnym prowadzeniu spraw.</p>
  </div>
</section>

<section class="section page-reveal">
  <div class="container editorial-split">
    <div>
      <h2>Kim jesteśmy</h2>
    </div>
    <div class="rich-text">
      <p>Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. wspiera klientów biznesowych i indywidualnych w sprawach wymagających precyzyjnego doradztwa, reprezentacji i dobrej organizacji procesu.</p>
      <p>Zespół kancelarii tworzą radcowie prawni, adwokaci, specjaliści prawni i administracyjni. Dzięki temu możliwe jest prowadzenie wielu spraw równolegle, bez utraty kontroli nad szczegółem.</p>
    </div>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container timeline">
    <div>
      <span>01</span>
      <strong>Rozpoznanie sprawy</strong>
      <p>Ustalamy fakty, dokumenty i interes klienta.</p>
    </div>
    <div>
      <span>02</span>
      <strong>Dobór rozwiązania</strong>
      <p>Przedstawiamy rekomendację i możliwe warianty działania.</p>
    </div>
    <div>
      <span>03</span>
      <strong>Prowadzenie</strong>
      <p>Realizujemy przyjętą strategię i dbamy o komunikację.</p>
    </div>
  </div>
</section>

<section class="section page-reveal">
  <div class="container editorial-split">
    <div>
      <span class="section-kicker">Dokumenty</span>
      <h2>Standardy i informacje formalne.</h2>
    </div>
    <div class="document-list">
      <?php foreach ($site['documents'] as $doc): ?>
        <a class="document-row" href="/dokumenty/<?= e($doc['slug']) ?>/">
          <strong><?= e($doc['title']) ?></strong>
          <span><?= e($doc['summary']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
