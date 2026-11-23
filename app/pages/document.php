<section class="page-hero">
  <div class="container page-intro" data-reveal>
    <span class="section-kicker">Dokument</span>
    <h1><?= e($document['title']) ?></h1>
    <p><?= e($document['summary']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container editorial-split archive-note">
    <div data-reveal>
      <h2>Informacja</h2>
    </div>
    <div class="rich-text">
      <p>Aktualna treść dokumentu jest przygotowywana do publikacji. Aby otrzymać obowiązującą wersję, skontaktuj się z kancelarią.</p>
      <div class="editorial-actions">
        <a class="button secondary" href="/dokumenty/">Wróć do dokumentów</a>
        <a class="text-action" href="/kontakt/">Skontaktuj się z kancelarią <?= icon('arrow', 'inline-arrow') ?></a>
      </div>
    </div>
  </div>
</section>
