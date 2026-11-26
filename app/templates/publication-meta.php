<?php
$published = publication_date($publication['date'] ?? null);
$category = is_string($publication['category'] ?? null) ? trim($publication['category']) : '';
?>
<?php if ($published || $category !== ''): ?>
  <p class="publication-meta">
    <?php if ($published): ?>
      <time datetime="<?= e($published->format('Y-m-d')) ?>"><?= e($published->format('d.m.Y')) ?></time>
    <?php endif; ?>
    <?php if ($category !== ''): ?>
      <span><?= e($category) ?></span>
    <?php endif; ?>
  </p>
<?php endif; ?>
