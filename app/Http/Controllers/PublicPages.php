<?php

namespace App\Http\Controllers;

use App\Content\PublicationContent;
use App\Content\SiteContent;
use App\Support\PublicUrl;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicPages
{
    public function __construct(
        private readonly Request $request,
        private readonly SiteContent $content,
        private readonly PublicationContent $publications,
    ) {}

    public function home(): Response
    {
        $services = $this->content->read('services');
        $team = $this->content->read('team');
        $partners = array_filter($team, static fn (array $person): bool => str_starts_with(mb_strtolower($person['role']), 'wspólnik'));
        $publications = $this->publications->home($this->content->publishedArticles());

        return $this->page('home', 'Kancelaria Radców Prawnych', compact('services', 'partners', 'publications'));
    }

    public function about(): Response
    {
        return $this->page('about', 'O kancelarii');
    }

    public function services(): Response
    {
        return $this->page('services', 'Specjalizacje', ['services' => $this->content->read('services')]);
    }

    public function service(string $slug): Response
    {
        $services = $this->content->read('services');
        $service = SiteContent::findBySlug($services, $slug);

        return $service === null ? $this->notFound() : $this->page('service', $service['title'], [
            'services' => $services,
            'service' => $service,
            'description' => $service['summary'],
        ]);
    }

    public function team(): Response
    {
        $team = $this->content->read('team');
        $teamGroups = [
            'leadership' => ['heading' => 'Wspólnicy i dyrekcja', 'people' => []],
            'coordinators' => ['heading' => 'Koordynatorzy zespołu', 'people' => []],
            'lawyers' => ['heading' => 'Prawnicy', 'people' => []],
            'support' => ['heading' => 'Specjaliści i administracja', 'people' => []],
        ];
        foreach ($team as $person) {
            [$title] = explode(' · ', mb_strtolower($person['role']), 2);
            $group = match (true) {
                str_starts_with($title, 'wspólnik'), str_starts_with($title, 'dyrektor') => 'leadership',
                str_starts_with($title, 'koordynator') => 'coordinators',
                in_array($title, ['radca prawny', 'adwokat'], true) => 'lawyers',
                default => 'support',
            };
            $teamGroups[$group]['people'][] = $person;
        }

        return $this->page('team', 'Zespół', compact('teamGroups'));
    }

    public function career(): Response
    {
        $recruitment = $this->content->read('recruitment');
        $vacancies = array_filter($recruitment['vacancies'], static fn (array $vacancy): bool => ($vacancy['status'] ?? '') === 'open');

        return $this->page('career', 'Kariera', compact('vacancies'));
    }

    public function legacyArchive(): Response
    {
        return PublicUrl::redirect($this->request, '/artykuly/');
    }

    public function articles(): Response
    {
        return $this->page('articles', 'Artykuły', $this->publications->archive($this->content->publishedArticles()) + [
            'description' => 'Publikacje prawne kancelarii Jaroch-Konwent Pakos. Analizy i komentarze z praktyki radców prawnych we Wrocławiu.',
        ]);
    }

    public function article(string $slug): Response
    {
        $articles = $this->content->publishedArticles();
        $article = SiteContent::findBySlug($articles, $slug);
        if ($article === null) {
            return $this->notFound();
        }

        return $this->page('article', $article['title'], $this->publications->detail($article) + [
            'article' => $article,
            'related' => $this->publications->related($articles, $article),
            'description' => $article['summary'] ?? null,
        ]);
    }

    public function contact(): Response
    {
        $site = $this->content->read('site');
        $hours = $site['hours'];
        $weekdays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek'];
        $hasUniformWeek = count($hours) === count($weekdays)
            && !array_diff($weekdays, array_keys($hours))
            && count(array_unique($hours)) === 1;
        $directions = 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode(implode(', ', $site['address']) . ', Polska');

        return $this->page('contact', 'Kontakt', compact('hours', 'weekdays', 'hasUniformWeek', 'directions'));
    }

    public function documents(): Response
    {
        return $this->page('documents', 'Dokumenty', ['robots' => 'noindex,follow']);
    }

    public function document(string $slug): Response
    {
        $documents = $this->content->read('site')['documents'] ?? [];
        $document = SiteContent::findBySlug($documents, $slug);

        return $document === null ? $this->notFound() : $this->page('document', $document['title'], [
            'document' => $document,
            'description' => $document['summary'],
            'robots' => 'noindex,follow',
        ]);
    }

    public function notFound(): Response
    {
        return $this->page('404', 'Nie znaleziono strony', ['robots' => 'noindex,follow'], 404);
    }

    private function page(string $page, string $title, array $data = [], int $status = 200): Response
    {
        $site = $this->content->read('site');
        $path = PublicUrl::path($this->request);

        return response()->view('pages.'.$page, [
            ...$data,
            'site' => $site,
            'path' => $path,
            'title' => $title.' — '.($site['name'] ?? 'Kancelaria'),
            'description' => $data['description'] ?? 'Jaroch-Konwent Pakos Kancelaria Radców Prawnych. Obsługa prawna firm i klientów indywidualnych we Wrocławiu.',
            'canonical' => rtrim((string) ($site['url'] ?? ''), '/').$path,
            'page' => $page,
        ], $status);
    }
}
