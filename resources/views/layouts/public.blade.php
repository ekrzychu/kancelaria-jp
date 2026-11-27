<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">
  <link rel="canonical" href="{{ $canonical }}">
  <meta property="og:type" content="{{ $page === 'article' ? 'article' : 'website' }}">
  <meta property="og:locale" content="pl_PL">
  <meta property="og:site_name" content="{{ $site['name'] }}">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:url" content="{{ $canonical }}">
  @if (isset($robots))
    <meta name="robots" content="{{ $robots }}">
  @endif
  <link rel="stylesheet" href="/assets/css/style.css?v={{ filemtime(public_path('assets/css/style.css')) }}">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body @if ($path === '/')class="homepage"@endif>
@include('partials.header')
<main id="main" tabindex="-1">
  @include('partials.floating-contact')
  @yield('content')
</main>
@include('partials.footer')
<script src="/assets/js/main.js?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>
</body>
</html>
