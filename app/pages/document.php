<section class="page-hero page-reveal">
  <div class="container narrow">
    <span class="section-kicker">Dokument</span>
    <h1><?= e($document['title']) ?></h1>
    <p><?= e($document['summary']) ?></p>
  </div>
</section>

<section class="section page-reveal">
  <div class="container editorial-split">
    <div>
      <h2>Informacja</h2>
    </div>
    <div class="rich-text">
      <p>Aktualna treść dokumentu jest przygotowywana do publikacji. Aby otrzymać obowiązującą wersję, skontaktuj się z kancelarią.</p>
      <p><a class="text-action" href="/kontakt/">Skontaktuj się z kancelarią <?= icon('arrow', 'inline-arrow') ?></a></p>
      <p><a class="button secondary" href="/dokumenty/">Wróć do dokumentów</a></p>
    </div>
  </div>
</section>
