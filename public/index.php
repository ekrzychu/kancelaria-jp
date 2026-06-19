<?php
require __DIR__ . '/../app/bootstrap.php';

$path = current_path();
$vars = compact('site', 'services', 'team', 'articles');

switch (true) {
    case $path === '/':
        render('home', $vars + [
            'title' => page_title('Kancelaria Radców Prawnych', $site),
        ]);
        break;

    case $path === '/o-kancelarii/':
        render('about', $vars + [
            'title' => page_title('O kancelarii', $site),
        ]);
        break;

    case $path === '/specjalizacje/':
        render('services', $vars + [
            'title' => page_title('Specjalizacje', $site),
        ]);
        break;

    case preg_match('#^/specjalizacje/([^/]+)/$#', $path, $m):
        $service = find_by_slug($services, $m[1]);
        if (!$service) {
            http_response_code(404);
            render('404', $vars);
            break;
        }

        render('service', $vars + [
            'service' => $service,
            'title' => page_title($service['title'], $site),
            'description' => $service['summary'],
        ]);
        break;

    case $path === '/zespol/':
        render('team', $vars + [
            'title' => page_title('Zespół', $site),
        ]);
        break;

    case $path === '/kariera/':
        render('career', $vars + [
            'title' => page_title('Kariera', $site),
        ]);
        break;

    case $path === '/artykuly/':
        render('articles', $vars + [
            'title' => page_title('Artykuły', $site),
            'robots' => 'noindex,follow',
        ]);
        break;

    case $path === '/kontakt/':
        render('contact', $vars + [
            'title' => page_title('Kontakt', $site),
        ]);
        break;

    case $path === '/dokumenty/':
        render('documents', $vars + [
            'title' => page_title('Dokumenty', $site),
        ]);
        break;

    case preg_match('#^/dokumenty/([^/]+)/$#', $path, $m):
        $document = find_by_slug($site['documents'] ?? [], $m[1]);
        if (!$document) {
            http_response_code(404);
            render('404', $vars);
            break;
        }

        render('document', $vars + [
            'document' => $document,
            'title' => page_title($document['title'], $site),
            'description' => $document['summary'],
            'robots' => 'noindex,follow',
        ]);
        break;

    default:
        http_response_code(404);
        render('404', $vars + [
            'title' => page_title('Nie znaleziono strony', $site),
        ]);
}
