</main>
<?php if ($path === '/'): ?>
<div class="home-ending" data-home-stage="contact" data-home-surface="burgundy">
  <div class="home-stage-viewport">
    <div class="home-stage-backdrop" aria-hidden="true"></div>
    <div class="home-stage-content">
      <section class="home-contact home-section" aria-labelledby="home-contact-title" data-home-surface="burgundy">
        <div class="home-frame home-contact-layout" data-reveal="sequence">
          <h2 id="home-contact-title">Każda sprawa zaczyna się od&nbsp;rozmowy.</h2>
          <div class="home-contact-detail">
            <p>Przedstaw nam swoją sytuację. Pomożemy ustalić możliwe kierunki dalszego działania.</p>
            <a class="button home-contact-action" href="/kontakt/" data-home-surface="light">Skontaktuj się z&nbsp;kancelarią</a>
          </div>
        </div>
      </section>
<?php endif; ?>
<footer class="site-footer"<?= $path === '/' ? ' data-home-surface="burgundy"' : '' ?>>
  <?php if ($path !== '/'): ?>
    <div class="container footer-callout">
      <h2>Porozmawiajmy o sprawie.</h2>
      <a class="text-action" href="/kontakt/">Skontaktuj się z kancelarią</a>
    </div>
  <?php endif; ?>
  <div class="container footer-grid">
    <div class="footer-identity">
      <a class="footer-brand" href="/"><?= e($site['name']) ?></a>
      <p><?= e($site['tagline']) ?></p>
      <ul class="social-links" aria-label="Portale społecznościowe">
        <?php foreach ($site['socials'] as $label => $href): ?>
          <li><a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-contact">
      <address>
        <p><?= implode('<br>', array_map('e', $site['address'])) ?></p>
        <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a><br>
        <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
      </address>
    </div>

    <nav class="footer-nav" aria-label="Menu w stopce">
      <ul>
        <?php foreach ($site['nav'] as $item): ?>
          <li><a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>

  <div class="container legal-row">
    <p>© <?= date('Y') ?> <?= e($site['legal_name']) ?>. Wszelkie prawa zastrzeżone.</p>
    <ul>
      <li><a href="/dokumenty/">Dokumenty</a></li>
      <li><a href="/dokumenty/polityka-prywatnosci/">Polityka prywatności</a></li>
    </ul>
  </div>
</footer>
<?php if ($path === '/'): ?>
    </div>
  </div>
</div>
<?php endif; ?>
<script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/main.js') ?>"></script>
</body>
</html>
