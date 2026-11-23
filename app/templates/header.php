<?php
$title = $title ?? page_title('Kancelaria Radców Prawnych', $site);
$description = $description ?? 'Jaroch-Konwent Pakos Kancelaria Radców Prawnych. Obsługa prawna firm i klientów indywidualnych we Wrocławiu.';
$robots = $robots ?? null;
$canonical = canonical_url($site);
$path = current_path();
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="pl_PL">
  <meta property="og:site_name" content="<?= e($site['name']) ?>">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <?php if ($robots): ?>
    <meta name="robots" content="<?= e($robots) ?>">
  <?php endif; ?>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>
<a class="skip-link" href="#main">Przejdź do treści</a>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="/" aria-label="<?= e($site['name']) ?> — strona główna"<?= $path === '/' ? ' aria-current="page"' : '' ?>>
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 76 46" role="img" focusable="false" aria-hidden="true">
          <path d="M 23 7.2 V 33 c 0 1 0 2 -0.2 3 c -0.5 4 -2.2 7.4 -5 9.9 c 0.9 -0.2 1.8 -0.6 2.7 -1 c 5.7 -2.5 8.8 -6.9 9.6 -13 l 0.1 -3 V 0 L 23 7.2 Z M 68.7 0 v 23 a 12.8 12.8 0 0 0 0 -23 Z M 45.9 7.2 v 38.6 l 7.2 -7.2 V 0 l -7.2 7.2 Z M 7.3 46 V 23 a 12.9 12.9 0 0 0 0 23 Z M 76 38.7 V 46 h -7.3 l 7.3 -7.3 Z M 0 7.3 V 0 h 7.3 L 0 7.3 Z"></path>
        </svg>
      </span>
      <span class="brand-text">
        <strong><?= e($site['name']) ?></strong>
        <small><?= e($site['tagline']) ?></small>
      </span>
    </a>
    <button class="menu-button" type="button" aria-controls="main-nav" aria-expanded="false" hidden>Menu</button>
    <nav class="main-nav" id="main-nav" aria-label="Menu główne">
      <?php foreach ($site['nav'] as $item): ?>
        <?php $current = $path === $item['url'] ? 'page' : (is_current_section($item['url']) ? 'location' : null); ?>
        <a class="nav-link" href="<?= e($item['url']) ?>"<?= $current ? ' aria-current="' . e($current) . '"' : '' ?>><?= e($item['label']) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main id="main" tabindex="-1">
