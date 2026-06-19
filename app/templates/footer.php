</main>
<footer class="site-footer">
  <section class="footer-contact">
    <div class="container footer-contact-inner">
      <div>
        <span class="section-kicker">Kontakt</span>
        <h2>Porozmawiajmy o sprawie.</h2>
      </div>
      <div class="footer-contact-links">
        <a href="tel:<?= e($site['phone_href']) ?>">
          <?= icon('phone') ?>
          <?= e($site['phone']) ?>
        </a>
        <a href="mailto:<?= e($site['email']) ?>">
          <?= icon('mail') ?>
          <?= e($site['email']) ?>
        </a>
      </div>
    </div>
  </section>

  <div class="container footer-grid">
    <div class="footer-about">
      <a class="footer-brand" href="/"><?= e($site['name']) ?></a>
      <p>
        <?= e($site['legal_name']) ?><br>
        <?php foreach ($site['address'] as $line): ?>
          <?= e($line) ?><br>
        <?php endforeach; ?>
      </p>
    </div>

    <div>
      <h3>Strony</h3>
      <ul>
        <?php foreach ($site['nav'] as $item): ?>
          <li><a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="/dokumenty/">Dokumenty</a></li>
      </ul>
    </div>

    <div>
      <h3>Specjalizacje</h3>
      <ul>
        <?php foreach (array_slice($services, 0, 6) as $service): ?>
          <li><a href="/specjalizacje/<?= e($service['slug']) ?>/"><?= e($service['title']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="/specjalizacje/">Wszystkie specjalizacje</a></li>
      </ul>
    </div>

    <div>
      <h3>Portale społecznościowe</h3>
      <p class="social-links">
        <?php foreach ($site['socials'] as $label => $href): ?>
          <a href="<?= e($href) ?>" rel="noopener"><?= e($label) ?></a>
        <?php endforeach; ?>
      </p>
    </div>
  </div>

  <div class="container legal-row">
    <p>© <?= date('Y') ?> <?= e($site['legal_name']) ?>. Wszelkie prawa zastrzeżone.</p>
    <a href="/dokumenty/polityka-prywatnosci/">Polityka prywatności</a>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>
