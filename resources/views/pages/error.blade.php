<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow">
  <title>{{ $heading }} — Jaroch-Konwent Pakos</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="container error-page">
  <p class="section-kicker">Błąd {{ $status }}</p>
  <h1>{{ $heading }}</h1>
  <p>{{ $message }}</p>
  <div class="error-actions">
    <a class="button" href="/">Wróć na stronę główną</a>
    <a class="text-action" href="/kontakt/">Kontakt z kancelarią</a>
  </div>
</main>
</body>
</html>
