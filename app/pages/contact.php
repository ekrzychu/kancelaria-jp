<?php
$formFields = $formStatus['fields'] ?? [];
$hours = $site['hours'];
$weekdays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek'];
$hasUniformWeek = count($hours) === count($weekdays)
    && !array_diff($weekdays, array_keys($hours))
    && count(array_unique($hours)) === 1;
$directions = 'https://www.google.com/maps/dir/?api=1&destination='
    . rawurlencode(implode(', ', $site['address']) . ', Polska');
$contactArrow = <<<'SVG'
<svg class="contact-arrow" viewBox="0 0 28 18" width="28" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 9h24m-7-7 7 7-7 7"/></svg>
SVG;
?>

<section class="contact-hero" aria-labelledby="contact-title">
  <div class="contact-frame contact-hero-layout">
    <div data-reveal="subtle">
      <span class="section-kicker">Kontakt</span>
      <h1 id="contact-title">Porozmawiajmy o&nbsp;Twojej sprawie.</h1>
    </div>
    <div class="contact-intro" data-reveal="subtle">
      <p>Skontaktuj się z&nbsp;nami telefonicznie, mailowo lub za pomocą formularza. Spotkania odbywają się po wcześniejszym umówieniu.</p>
      <a class="text-action contact-action" href="#formularz"><span>Przejdź do formularza</span><?= $contactArrow ?></a>
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
            <dd><a class="text-action contact-action" href="tel:<?= e($site['phone_href']) ?>"><span><?= e($site['phone']) ?></span><?= $contactArrow ?></a></dd>
          </div>
          <div>
            <dt>E-mail</dt>
            <dd><a class="text-action contact-action" href="mailto:<?= e($site['email']) ?>"><span><?= e($site['email']) ?></span><?= $contactArrow ?></a></dd>
          </div>
        </dl>
      </div>
      <dl class="contact-office-details" data-reveal="subtle">
        <div>
          <dt>Adres</dt>
          <dd>
            <address>
              <strong><?= e($site['legal_name']) ?></strong><br>
              <?= implode('<br>', array_map('e', $site['address'])) ?>
            </address>
          </dd>
        </div>
        <div>
          <dt>Godziny pracy</dt>
          <dd class="contact-hours">
            <?php if ($hasUniformWeek): ?>
              <span><span>Poniedziałek – piątek</span><span><?= e($hours[$weekdays[0]]) ?></span></span>
            <?php else: ?>
              <?php foreach ($hours as $day => $time): ?>
                <span><span><?= e($day) ?></span><span><?= e($time) ?></span></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </dd>
        </div>
      </dl>
    </div>

    <form class="contact-form" id="formularz" aria-labelledby="contact-form-title" tabindex="-1" method="post" action="/kontakt/"<?= $formStatus ? '' : ' data-reveal="subtle"' ?>>
      <h2 id="contact-form-title">Napisz do nas</h2>
      <?php if ($formStatus): ?>
        <p class="form-status <?= $formStatus['ok'] ? 'ok' : 'error' ?>" role="<?= $formStatus['ok'] ? 'status' : 'alert' ?>" tabindex="-1" autofocus><?= e($formStatus['message']) ?></p>
      <?php endif; ?>

      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <label class="form-honeypot" aria-hidden="true">
        Strona internetowa
        <input name="website" autocomplete="off" tabindex="-1">
      </label>
      <div class="contact-fields">
        <label>
          Imię i nazwisko
          <input name="name" autocomplete="name" maxlength="120" value="<?= e($formFields['name'] ?? '') ?>" required>
        </label>
        <label>
          Adres e-mail
          <input name="email" type="email" autocomplete="email" maxlength="180" value="<?= e($formFields['email'] ?? '') ?>" required>
        </label>
      </div>
      <label>
        <span>Telefon <small>(opcjonalnie)</small></span>
        <input name="phone" type="tel" autocomplete="tel" maxlength="40" value="<?= e($formFields['phone'] ?? '') ?>">
      </label>
      <label>
        Opis sprawy
        <textarea name="message" rows="6" maxlength="4000" required><?= e($formFields['message'] ?? '') ?></textarea>
      </label>
      <p class="privacy-note">Dane z formularza służą wyłącznie do kontaktu w sprawie zgłoszenia. Szczegóły opisuje <a href="/dokumenty/polityka-prywatnosci/">polityka prywatności</a>.</p>
      <button class="button contact-submit" type="submit">Wyślij wiadomość <?= $contactArrow ?></button>
    </form>
  </div>
</section>

<section class="contact-location" aria-labelledby="contact-location-title">
  <div class="contact-frame contact-location-layout">
    <div class="contact-location-copy" data-reveal="subtle">
      <h2 id="contact-location-title">Odwiedź nas we&nbsp;Wrocławiu.</h2>
      <address><?= implode('<br>', array_map('e', $site['address'])) ?></address>
      <p>Spotkania odbywają się po wcześniejszym umówieniu.</p>
      <a class="text-action contact-action" href="<?= e($directions) ?>"><span>Wyznacz trasę</span><?= $contactArrow ?></a>
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
