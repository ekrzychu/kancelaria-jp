</main>
<footer class="site-footer">
  <div class="container footer-callout">
    <h2>Porozmawiajmy o sprawie.</h2>
    <a class="text-action" href="/kontakt/">Skontaktuj się z kancelarią</a>
  </div>
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
<script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/main.js') ?>"></script>
</body>
</html>
