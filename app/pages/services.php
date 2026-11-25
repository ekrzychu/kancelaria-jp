<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Specjalizacje</span>
    <h1>Zakres wsparcia prawnego.</h1>
  </div>
</section>

<section class="section" aria-label="Katalog specjalizacji">
  <div class="container">
    <div class="filter-line" data-reveal>
      <label>
        <?= icon('search') ?>
        <span class="visually-hidden">Szukaj specjalizacji</span>
        <input type="search" placeholder="Szukaj specjalizacji" autocomplete="off" data-service-search aria-controls="service-list">
      </label>
      <p class="filter-status" data-service-status aria-live="polite"></p>
    </div>

    <div class="editorial-list" id="service-list" data-service-list data-reveal-children>
      <?php foreach ($services as $service): ?>
        <?php $searchText = text_lower($service['title'] . ' ' . $service['summary'] . ' ' . $service['category']); ?>
        <a
          class="editorial-row"
          data-service-row
          data-title="<?= e($searchText) ?>"
          href="/specjalizacje/<?= e($service['slug']) ?>/"
        >
          <span class="row-heading">
            <span class="row-meta"><?= e($service['category']) ?></span>
            <span class="row-title"><?= e($service['title']) ?></span>
          </span>
          <span class="row-summary"><?= e($service['summary']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
