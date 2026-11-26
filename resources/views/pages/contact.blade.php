@extends('layouts.public')

@section('content')
<section class="contact-hero" aria-labelledby="contact-title">
  <div class="contact-frame contact-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">Kontakt</span>
      <h1 id="contact-title">Porozmawiajmy o&nbsp;Twojej sprawie.</h1>
    </div>
    <div class="contact-intro" data-reveal="subtle">
      <p>Skontaktuj się z&nbsp;nami telefonicznie lub mailowo. Spotkania odbywają się po wcześniejszym umówieniu.</p>
      <a class="text-action contact-action" href="#formularz"><span>Przejdź do formularza</span>@include('partials.action-arrow', ['arrowClass' => 'contact-arrow'])</a>
    </div>
  </div>
</section>

<section class="contact-enquiries" aria-labelledby="contact-details-title">
  <div class="contact-frame contact-layout">
    <div class="contact-details">
      <div data-reveal="subtle">
        <h2 id="contact-details-title">Dane kontaktowe</h2>
        <dl class="contact-methods">
          <div>
            <dt>Telefon</dt>
            <dd><a class="text-action contact-action" href="tel:{{ $site['phone_href'] }}"><span>{{ $site['phone'] }}</span>@include('partials.action-arrow', ['arrowClass' => 'contact-arrow'])</a></dd>
          </div>
          <div>
            <dt>E-mail</dt>
            <dd><a class="text-action contact-action" href="mailto:{{ $site['email'] }}"><span>{{ $site['email'] }}</span>@include('partials.action-arrow', ['arrowClass' => 'contact-arrow'])</a></dd>
          </div>
        </dl>
      </div>
      <dl class="contact-office-details" data-reveal="subtle">
        <div>
          <dt>Adres</dt>
          <dd>
            <address>
              <strong>{{ $site['legal_name'] }}</strong><br>
              @include('partials.office-address')
            </address>
          </dd>
        </div>
        <div>
          <dt>Godziny pracy</dt>
          <dd class="contact-hours">
            @if ($hasUniformWeek)
              <span><span>Poniedziałek – piątek</span><span>{{ $hours[$weekdays[0]] }}</span></span>
            @else
              @foreach ($hours as $day => $time)
                <span><span>{{ $day }}</span><span>{{ $time }}</span></span>
              @endforeach
            @endif
          </dd>
        </div>
      </dl>
    </div>

    <form class="contact-form" id="formularz" aria-labelledby="contact-form-title" aria-describedby="contact-form-status" tabindex="-1" method="post" action="/kontakt/" data-reveal="subtle">
      <div class="contact-form-heading">
        <h2 id="contact-form-title">Napisz do nas</h2>
        <p id="contact-form-status">Formularz jest tymczasowo niedostępny. Skontaktuj się z&nbsp;nami telefonicznie lub mailowo.</p>
      </div>
      <div class="contact-fields">
        <label class="contact-field">
          <span>Imię i nazwisko <span class="contact-required" aria-hidden="true">*</span></span>
          <input name="name" autocomplete="name" maxlength="120" disabled required>
        </label>
        <label class="contact-field">
          <span>Adres e-mail <span class="contact-required" aria-hidden="true">*</span></span>
          <input name="email" type="email" autocomplete="email" maxlength="180" disabled required>
        </label>
        <label class="contact-field">
          <span>Telefon <small>(opcjonalnie)</small></span>
          <input name="phone" type="tel" autocomplete="tel" maxlength="40" disabled>
        </label>
        <label class="contact-field">
          <span>Opis sprawy <span class="contact-required" aria-hidden="true">*</span></span>
          <textarea name="message" rows="5" maxlength="4000" disabled required></textarea>
        </label>
      </div>
      <div class="contact-form-footer">
        <p class="privacy-note">Dane z formularza służą wyłącznie do kontaktu w sprawie zgłoszenia. Szczegóły opisuje <a href="/dokumenty/polityka-prywatnosci/">polityka prywatności</a>.</p>
        <button class="button contact-submit" type="submit" disabled>Wyślij wiadomość @include('partials.action-arrow', ['arrowClass' => 'contact-arrow'])</button>
      </div>
    </form>
  </div>
</section>

<section class="contact-location" aria-labelledby="contact-location-title">
  <div class="contact-frame contact-location-layout">
    <div class="contact-location-copy" data-reveal="subtle">
      <h2 id="contact-location-title">Odwiedź nas we&nbsp;Wrocławiu.</h2>
      <address>@include('partials.office-address')</address>
      <p>Spotkania odbywają się po wcześniejszym umówieniu.</p>
      <a class="text-action contact-action" href="{{ $directions }}"><span>Wyznacz trasę</span>@include('partials.action-arrow', ['arrowClass' => 'contact-arrow'])</a>
    </div>
    <figure class="contact-photo" data-reveal="subtle" data-home-surface="photo">
      <img src="/assets/img/office-entrance-760.webp"
        srcset="/assets/img/office-entrance-760.webp 760w, /assets/img/office-entrance-1280.webp 1280w"
        sizes="(min-width: 68rem) 27rem, (min-width: 48rem) 42vw, 90vw"
        width="1280" height="1990" loading="lazy" decoding="async"
        alt="Drewniane drzwi wejściowe i numer 7–8 na kamiennej fasadzie przy placu Kościuszki">
    </figure>
  </div>
</section>
@endsection
