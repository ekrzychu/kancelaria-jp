<?php
$title = $title ?? page_title('Kancelaria Radców Prawnych', $site);
$description = $description ?? 'Jaroch-Konwent Pakos Kancelaria Radców Prawnych. Obsługa prawna firm i klientów indywidualnych we Wrocławiu.';
$robots = $robots ?? null;
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <?php if ($robots): ?>
    <meta name="robots" content="<?= e($robots) ?>">
  <?php endif; ?>
  <script>document.documentElement.classList.add('js');</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700&display=swap&subset=latin,latin-ext" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>
<a class="skip-link" href="#main">Przejdź do treści</a>
<header class="site-header" data-header>
  <div class="container header-inner">
    <a class="brand" href="/" aria-label="Jaroch-Konwent Pakos — strona główna">
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
    <button class="menu-button" type="button" data-menu-button aria-controls="main-nav" aria-expanded="false">Menu</button>
    <nav class="main-nav" id="main-nav" data-menu>
      <?php foreach ($site['nav'] as $item): ?>
        <a class="nav-link<?= is_active($item['url']) ?>" href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
      <?php endforeach; ?>
      <a class="nav-action" href="/kontakt/">Umów spotkanie</a>
    </nav>
  </div>
</header>
<main id="main">
