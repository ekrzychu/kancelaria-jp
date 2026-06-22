<?php $formFields = $formStatus['fields'] ?? []; ?>

<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">Kontakt</span>
    <h1>Umów spotkanie z kancelarią.</h1>
    <p>Spotkania odbywają się po wcześniejszym ustaleniu terminu. Najszybciej skontaktujesz się telefonicznie lub mailowo.</p>
  </div>
</section>

<section class="section page-reveal">
  <div class="container contact-layout">
    <div class="contact-details">
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
          <dd>
            <?php foreach ($site['hours'] as $day => $hours): ?>
              <?= e($day) ?>: <?= e($hours) ?><br>
            <?php endforeach; ?>
          </dd>
        </div>
      </dl>
    </div>

    <form class="contact-form" method="post" action="/kontakt/">
      <h2>Opisz krótko sprawę</h2>
      <?php if ($formStatus): ?>
        <p class="form-status <?= $formStatus['ok'] ? 'ok' : 'error' ?>" role="status" aria-live="polite"><?= e($formStatus['message']) ?></p>
      <?php endif; ?>

      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <label class="form-honeypot" aria-hidden="true">
        Strona internetowa
        <input name="website" autocomplete="off" tabindex="-1">
      </label>
      <label>
        Imię i nazwisko
        <input name="name" autocomplete="name" maxlength="120" value="<?= e($formFields['name'] ?? '') ?>" required>
      </label>
      <label>
        Adres e-mail
        <input name="email" type="email" autocomplete="email" maxlength="180" value="<?= e($formFields['email'] ?? '') ?>" required>
      </label>
      <label>
        Telefon
        <input name="phone" autocomplete="tel" maxlength="40" value="<?= e($formFields['phone'] ?? '') ?>">
      </label>
      <label>
        Opis sprawy
        <textarea name="message" rows="6" maxlength="4000" required><?= e($formFields['message'] ?? '') ?></textarea>
      </label>
      <p class="privacy-note">Dane z formularza służą wyłącznie do kontaktu w sprawie zgłoszenia. Szczegóły opisuje <a href="/dokumenty/polityka-prywatnosci/">polityka prywatności</a>.</p>
      <button class="button" type="submit">Wyślij zgłoszenie</button>
    </form>
  </div>
</section>
