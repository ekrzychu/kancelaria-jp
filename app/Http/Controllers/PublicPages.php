<?php

namespace App\Http\Controllers;

use App\Content\PublicationContent;
use App\Content\SiteContent;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Support\PublicUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $services = $this->content->specializations();
        $partners = $this->content->homepagePartners();
        $publications = $this->publications->home($this->content->publishedArticles());

        return $this->page('home', 'Kancelaria Radców Prawnych', compact('services', 'partners', 'publications'));
    }

    public function about(): Response
    {
        return $this->page('about', 'O kancelarii');
    }

    public function services(): Response
    {
        return $this->page('services', 'Specjalizacje', ['services' => $this->content->specializations()]);
    }

    public function service(string $slug): Response
    {
        $services = $this->content->specializations();
        $service = SiteContent::findBySlug($services, $slug);

        return $service === null ? $this->notFound() : $this->page('service', $service['title'], [
            'services' => $services,
            'service' => $service,
            'description' => $service['summary'],
        ]);
    }

    public function team(): Response
    {
        return $this->page('team', 'Zespół', ['teamGroups' => $this->content->teamGroups()]);
    }

    public function career(): Response
    {
        return $this->page('career', 'Kariera', ['vacancies' => $this->content->openVacancies()]);
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
        $article = $this->content->publishedArticle($slug);
        if ($article === null) {
            return $this->notFound();
        }

        return $this->articlePage($article);
    }

    public function renderArticle(Article $article, bool $preview): Response
    {
        return $this->articlePage(SiteContent::articleData($article), $preview);
    }

    private function articlePage(array $article, bool $preview = false): Response
    {
        $publicOrigin = $preview ? rtrim(config('hosts.public_url'), '/') : '';

        return $this->page('article', $article['title'], $this->publications->detail($article, $publicOrigin) + [
            'article' => $article,
            'related' => $this->publications->related($this->content->publishedArticles(), $article),
            'description' => $article['summary'] ?? null,
        ] + ($preview ? [
            'previewPublicOrigin' => $publicOrigin,
            'robots' => 'noindex,nofollow',
        ] : []));
    }

    public function contact(int $status = 200, array $form = []): Response
    {
        $site = $this->content->site();
        $hours = $site['hours'];
        $weekdays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek'];
        $hasUniformWeek = count($hours) === count($weekdays)
            && !array_diff($weekdays, array_keys($hours))
            && count(array_unique($hours)) === 1;
        $directions = 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode(implode(', ', $site['address']) . ', Polska');

        $response = $this->page('contact', 'Kontakt', $form + compact('hours', 'weekdays', 'hasUniformWeek', 'directions'), $status);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    public function submitContact(): Response
    {
        $website = $this->request->post('website');
        if ($website !== null && $website !== '') {
            return $this->contact(422, ['formError' => 'Nie udało się przyjąć wiadomości. Spróbuj ponownie.']);
        }

        $limits = ['name' => 120, 'email' => 180, 'phone' => 40, 'message' => 4000];
        $input = array_intersect_key($this->request->post(), $limits);
        $text = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (! mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0"))) {
                $fail('Pole zawiera niedozwolone znaki.');
            }
        };
        $validator = Validator::make($input, [
            'name' => ['bail', 'required', 'string', $text, 'max:120'],
            'email' => ['bail', 'required', 'string', 'max:180', 'regex:/\A[\x21-\x7e]+\z/', 'email:strict'],
            'phone' => ['bail', 'nullable', 'string', $text, 'max:40'],
            'message' => ['bail', 'required', 'string', $text, 'max:4000'],
        ], [
            'required' => 'Uzupełnij pole „:attribute”.',
            'string' => 'Wpisz tekst w polu „:attribute”.',
            'max' => 'Pole „:attribute” może zawierać najwyżej :max znaków.',
            'email' => 'Podaj poprawny adres e-mail.',
            'email.regex' => 'Podaj poprawny adres e-mail.',
        ], ['name' => 'Imię i nazwisko', 'email' => 'Adres e-mail', 'phone' => 'Telefon', 'message' => 'Opis sprawy']);

        if ($validator->fails()) {
            $values = [];
            foreach ($input as $field => $value) {
                if (is_string($value) && mb_check_encoding($value, 'UTF-8') && ! str_contains($value, "\0")) {
                    $values[$field] = mb_substr($value, 0, $limits[$field]);
                }
            }

            return $this->contact(422, ['formErrors' => $validator->errors(), 'formValues' => $values]);
        }

        ContactMessage::create($validator->validated() + ['next_attempt_at' => now()]);

        return redirect('/kontakt/#formularz', 303, ['Cache-Control' => 'private, no-store'])
            ->with('contact_received', true);
    }

    public function documents(): Response
    {
        return $this->page('documents', 'Dokumenty', ['robots' => 'noindex,follow']);
    }

    public function document(string $slug): Response
    {
        $documents = $this->content->site()['documents'] ?? [];
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
        $site = $this->content->site();
        $path = isset($data['previewPublicOrigin']) ? '/'.$data['article']['slug'].'/' : PublicUrl::path($this->request);

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
