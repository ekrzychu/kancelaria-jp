<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">Specjalizacje</span>
    <h1>Zakres wsparcia prawnego.</h1>
    <p>Najważniejsze obszary praktyki kancelarii zebrane w czytelnej strukturze.</p>
  </div>
</section>

<section class="section muted page-reveal">
  <div class="container">
    <div class="filter-line">
      <label>
        <?= icon('search') ?>
        <input type="search" placeholder="Szukaj specjalizacji" data-service-search>
      </label>
    </div>

    <div class="practice-list" data-service-list>
      <?php foreach ($services as $i => $service): ?>
        <?php $searchText = mb_strtolower($service['title'] . ' ' . $service['summary'] . ' ' . $service['category']); ?>
        <a
          class="practice-row"
          data-service-row
          data-title="<?= e($searchText) ?>"
          href="/specjalizacje/<?= e($service['slug']) ?>/"
        >
          <span class="row-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="row-main">
            <small><?= e($service['category']) ?></small>
            <strong><?= e($service['title']) ?></strong>
            <em><?= e($service['summary']) ?></em>
          </span>
          <span class="row-link">Otwórz</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
