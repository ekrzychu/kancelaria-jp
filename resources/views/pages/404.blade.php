@extends('layouts.public')

@section('content')
<section class="container error-page" aria-labelledby="error-title">
  <p class="section-kicker">Błąd 404</p>
  <h1 id="error-title">Nie znaleziono strony.</h1>
  <p>Sprawdź adres lub wybierz jedną z poniższych stron.</p>
  <div class="error-actions">
    <a class="button" href="/">Wróć na stronę główną</a>
    <a class="text-action" href="/kontakt/">Kontakt z kancelarią</a>
  </div>
</section>
@endsection
