<?php
$publication = $article;
$summary = is_string($article['summary'] ?? null) ? trim($article['summary']) : '';
$author = is_string($article['author'] ?? null) ? trim($article['author']) : '';
$bodyHtml = $article['body_html'];
$sections = [];
$sectionCount = 0;

// Build navigation from the reviewed local HTML fragment.
if (class_exists(DOMDocument::class)) {
    $document = new DOMDocument();
    $fragment = '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $bodyHtml . '</body></html>';
    if ($document->loadHTML($fragment, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
        $body = $document->getElementsByTagName('body')->item(0);
        $xpath = new DOMXPath($document);
        $ids = [];
        foreach ($xpath->query('.//*[@id]', $body) as $node) {
            $id = $node->getAttribute('id');
            $ids[$id] = ($ids[$id] ?? 0) + 1;
        }

        $polish = array_combine(
            preg_split('//u', 'ąĄćĆęĘłŁńŃóÓśŚźŹżŻ', -1, PREG_SPLIT_NO_EMPTY),
            str_split('aAcCeElLnNoOsSzZzZ')
        );
        foreach ($xpath->query('.//h2[not(ancestor::blockquote)] | .//h3[not(ancestor::blockquote)]', $body) as $heading) {
            $label = trim(preg_replace('/\s+/u', ' ', $heading->textContent));
            if ($label === '') {
                continue;
            }
            $id = $heading->getAttribute('id');
            if ($id === '' || $ids[$id] > 1) {
                $slug = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', strtolower(strtr($label, $polish))), '-');
                $base = 'sekcja-' . ($slug !== '' ? $slug : 'artykulu');
                $id = $base;
                for ($suffix = 2; isset($ids[$id]); $suffix++) {
                    $id = $base . '-' . $suffix;
                }
                $heading->setAttribute('id', $id);
                $ids[$id] = 1;
            }
            $nested = $heading->tagName === 'h3';
            $sectionCount += (int) !$nested;
            $heading->setAttribute('tabindex', '-1');
            $sections[] = ['id' => $id, 'label' => $label, 'nested' => $nested];
        }

        $tables = $xpath->query('.//table', $body);
        foreach ($tables as $table) {
            $caption = trim($xpath->query('./caption', $table)->item(0)?->textContent ?? '');
            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'article-table-scroll');
            $wrapper->setAttribute('role', 'region');
            $wrapper->setAttribute('aria-label', $caption !== '' ? $caption : 'Tabela w artykule');
            $wrapper->setAttribute('tabindex', '0');
            $table->parentNode->insertBefore($wrapper, $table);
            $wrapper->appendChild($table);
        }

        if ($sections || $tables->length) {
            $bodyHtml = '';
            foreach ($body->childNodes as $node) {
                $bodyHtml .= $document->saveHTML($node);
            }
        }
    }
}

$related = [];
foreach ($articles as $entry) {
    $slug = $entry['slug'] ?? null;
    if (!is_string($slug) || $slug === $article['slug'] || !preg_match('/\A[a-z0-9-]+\z/', $slug)
        || !is_string($entry['title'] ?? null) || trim($entry['title']) === '') {
        continue;
    }
    $entry['related_date'] = publication_date($entry['date'] ?? null)?->format('Y-m-d') ?? '';
    $related[] = $entry;
}
usort($related, static fn (array $a, array $b): int =>
    $b['related_date'] <=> $a['related_date']
);
$related = array_slice($related, 0, 2);

$archiveLink = <<<'HTML'
<a class="text-action publication-back" href="/artykuly/">
  <svg class="publication-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M26 9H2m7-7L2 9l7 7"/></svg>
  <span>Wszystkie artykuły</span>
</a>
HTML;
?>
<article class="article-detail publication-frame" aria-labelledby="article-title">
  <div class="article-progress" aria-hidden="true" hidden></div>
  <header class="article-header" data-reveal="subtle">
    <div class="article-intro">
      <?= $archiveLink ?>
      <div class="article-meta">
        <?php require __DIR__ . '/../templates/publication-meta.php'; ?>
        <?php if ($author !== ''): ?>
          <p class="publication-meta"><?= e($author) ?></p>
        <?php endif; ?>
      </div>
      <h1 id="article-title"><?= e($article['title']) ?></h1>
      <?php if ($summary !== ''): ?>
        <p class="article-summary"><?= e($summary) ?></p>
      <?php endif; ?>
    </div>
  </header>
  <div class="article-reading">
    <?php if ($sectionCount >= 2): ?>
      <details class="article-contents" data-reveal="subtle">
        <summary>Spis treści</summary>
        <nav aria-label="Spis treści">
          <ul>
            <?php foreach ($sections as $section): ?>
              <li<?= $section['nested'] ? ' class="article-contents-subsection"' : '' ?>>
                <a href="#<?= e(rawurlencode($section['id'])) ?>"><?= e($section['label']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </details>
    <?php endif; ?>
    <div class="article-body">
      <?= $bodyHtml ?>
    </div>
  </div>
  <?php if ($related): ?>
    <section class="article-related" aria-labelledby="article-related-title">
      <header class="article-related-heading" data-reveal="subtle">
        <h2 id="article-related-title">Pozostałe publikacje</h2>
        <?= $archiveLink ?>
      </header>
      <div data-reveal-sequence>
        <?php foreach ($related as $index => $publication): ?>
          <?php $relatedSummary = is_string($publication['summary'] ?? null) ? trim($publication['summary']) : ''; ?>
          <a class="publication-entry editorial-row" data-reveal="publication-row"
             href="/<?= e($publication['slug']) ?>/" aria-labelledby="article-related-<?= $index ?>">
            <div class="publication-heading">
              <?php require __DIR__ . '/../templates/publication-meta.php'; ?>
              <h3 class="row-title" id="article-related-<?= $index ?>"><?= e($publication['title']) ?></h3>
            </div>
            <?php if ($relatedSummary !== ''): ?>
              <p class="publication-summary row-summary"><?= e($relatedSummary) ?></p>
            <?php endif; ?>
            <span class="service-row-arrow publication-row-arrow" aria-hidden="true">&rarr;</span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php else: ?>
    <nav class="article-navigation" aria-label="Nawigacja po artykułach">
      <?= $archiveLink ?>
    </nav>
  <?php endif; ?>
</article>
