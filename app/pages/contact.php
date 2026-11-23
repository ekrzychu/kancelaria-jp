<?php $formFields = $formStatus['fields'] ?? []; ?>

<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Kontakt</span>
    <h1>Umów spotkanie z kancelarią.</h1>
    <div>
      <p>Spotkania po wcześniejszym umówieniu. Zadzwoń lub napisz.</p>
      <a class="text-action contact-form-link" href="#formularz">Przejdź do formularza</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container contact-layout">
    <div class="contact-details">
      <h2 data-reveal>Dane kontaktowe</h2>
      <dl>
        <div>
          <dt><?= icon('pin') ?>Adres</dt>
          <dd>
            <?= e($site['legal_name']) ?><br>
            <?php foreach ($site['address'] as $line): ?>
              <?= e($line) ?><br>
            <?php endforeach; ?>
          </dd>
        </div>
        <div>
          <dt><?= icon('phone') ?>Telefon</dt>
          <dd><a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a></dd>
        </div>
        <div>
          <dt><?= icon('mail') ?>E-mail</dt>
          <dd><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></dd>
        </div>
        <div>
          <dt>Godziny pracy</dt>
          <dd class="contact-hours">
            <?php foreach ($site['hours'] as $day => $hours): ?>
              <span><span><?= e($day) ?></span> <span><?= e($hours) ?></span></span>
            <?php endforeach; ?>
          </dd>
        </div>
      </dl>
    </div>

    <form class="contact-form" id="formularz" aria-labelledby="contact-form-title" tabindex="-1" method="post" action="/kontakt/">
      <h2 id="contact-form-title" data-reveal>Opisz krótko sprawę</h2>
      <?php if ($formStatus): ?>
        <p class="form-status <?= $formStatus['ok'] ? 'ok' : 'error' ?>" role="<?= $formStatus['ok'] ? 'status' : 'alert' ?>" tabindex="-1"><?= e($formStatus['message']) ?></p>
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
      <button class="button" type="submit">Wyślij zgłoszenie</button>
    </form>

    <figure class="contact-photo" data-reveal="image">
      <picture>
        <source type="image/webp" srcset="/assets/img/office-reception-wide.webp">
        <img src="/assets/img/office-reception-wide.jpg" alt="Recepcja kancelarii z drewnianą ladą i monogramem JP" width="1800" height="900" loading="lazy" decoding="async">
      </picture>
    </figure>
  </div>
</section>
