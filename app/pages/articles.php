<?php
$publications = array_values(array_filter($articles, static fn ($entry) =>
    is_array($entry) && is_string($entry['title'] ?? null) && trim($entry['title']) !== ''
));
foreach ($publications as &$publication) {
    $publication['archive_date'] = publication_date($publication['date'] ?? null)?->format('Y-m-d') ?? '';
    $publication['archive_category'] = is_string($publication['category'] ?? null) ? trim($publication['category']) : '';
}
unset($publication);
usort($publications, static fn (array $a, array $b): int => $b['archive_date'] <=> $a['archive_date']);

$categories = array_unique(array_filter(array_column($publications, 'archive_category'), 'strlen'));
$years = array_unique(array_filter(array_map(static fn (array $entry): string => substr($entry['archive_date'], 0, 4), $publications), 'strlen'));
?>
<section class="publication-hero" aria-labelledby="publications-title">
  <div class="publication-frame publication-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">Artykuły</span>
      <h1 id="publications-title">Publikacje prawne.</h1>
    </div>
    <p data-reveal="subtle">Analizy i&nbsp;komentarze do zmian w&nbsp;prawie.</p>
  </div>
</section>

<section class="publication-archive<?= $publications ? '' : ' archive-empty' ?>" aria-label="Publikacje" data-publication-archive>
  <div class="publication-frame">
    <?php if ($publications): ?>
      <form class="publication-filters" role="search" aria-label="Szukaj i filtruj artykuły" data-publication-filters data-reveal="subtle" hidden>
        <div class="publication-search">
          <label>
            <?= icon('search') ?>
            <span class="visually-hidden">Szukaj artykułów</span>
            <input type="search" name="query" placeholder="Szukaj artykułów" autocomplete="off" aria-controls="publication-list">
          </label>
          <button class="publication-search-clear" type="button" aria-label="Wyczyść wyszukiwanie" data-publication-clear hidden>
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false">
              <path d="m4 4 8 8m0-8-8 8"/>
            </svg>
          </button>
        </div>

        <label class="publication-filter">
          <span>Tematyka</span>
          <select name="category" aria-controls="publication-list">
            <option value="">Wszystkie kategorie</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= e($category) ?>"><?= e($category) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="publication-filter">
          <span>Rok publikacji</span>
          <select name="year" aria-controls="publication-list">
            <option value="">Wszystkie lata</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= e($year) ?>"><?= e($year) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="publication-filter">
          <span>Sortowanie</span>
          <select name="sort" aria-controls="publication-list">
            <option value="newest">Od najnowszych</option>
            <option value="oldest">Od najstarszych</option>
            <option value="az">Alfabetycznie A–Z</option>
            <option value="za">Alfabetycznie Z–A</option>
          </select>
        </label>

        <div class="publication-filter-feedback">
          <p class="filter-status" data-publication-status role="status"></p>
          <button class="text-action publication-filter-reset" type="button" data-publication-reset hidden>Wyczyść filtry</button>
        </div>
      </form>
      <div id="publication-list" data-reveal-sequence>
        <?php foreach ($publications as $index => $publication): ?>
          <?php
          $date = $publication['archive_date'];
          $featured = $index === 0 && $date !== '';
          $summary = is_string($publication['summary'] ?? null) ? trim($publication['summary']) : '';
          $bodyHtml = is_string($publication['body_html'] ?? null) ? $publication['body_html'] : '';
          $body = preg_replace('~</(?:p|li|h[1-6]|div|blockquote|td|th|figcaption)\s*>|<br\b[^>]*>~i', ' ', $bodyHtml);
          $bodyText = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
          $searchText = preg_replace('/\s+/u', ' ', implode(' ', [$publication['title'], $summary, $publication['archive_category'], $bodyText]));
          $slug = $publication['slug'] ?? null;
          $linked = is_string($slug) && preg_match('/\A[a-z0-9-]+\z/D', $slug);
          $tag = $linked ? 'a' : 'div';
          $titleId = 'publication-title-' . $index;
          ?>
          <<?= $tag ?>
            class="publication-entry editorial-row"
            data-reveal="publication-row"
            data-publication-title="<?= e($publication['title']) ?>"
            data-publication-date="<?= e($date) ?>"
            data-publication-category="<?= e($publication['archive_category']) ?>"
            data-publication-search="<?= e($searchText) ?>"
            <?php if ($linked): ?>href="/<?= e($slug) ?>/" aria-labelledby="<?= e($titleId) ?>"<?php endif; ?>
          >
            <div class="publication-heading">
              <?php if ($featured): ?>
                <p class="section-kicker">Najnowsza publikacja</p>
              <?php endif; ?>
              <?php require __DIR__ . '/../templates/publication-meta.php'; ?>
              <h2 class="row-title" id="<?= e($titleId) ?>"><?= e($publication['title']) ?></h2>
            </div>
            <?php if ($summary !== ''): ?>
              <p class="publication-summary row-summary"><?= e($summary) ?></p>
            <?php endif; ?>
            <?php if ($linked): ?>
              <span class="service-row-arrow publication-row-arrow" aria-hidden="true">&rarr;</span>
            <?php endif; ?>
          </<?= $tag ?>>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="archive-note" data-reveal="subtle">
        <h2>Archiwum w przygotowaniu</h2>
        <p>W sprawie konkretnego zagadnienia napisz lub zadzwoń.</p>
        <a class="text-action" href="/kontakt/">Skontaktuj się</a>
      </div>
    <?php endif; ?>
  </div>
</section>
