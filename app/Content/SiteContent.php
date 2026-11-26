<?php

namespace App\Content;

use App\Models\Article;
use App\Models\JobOffer;
use App\Models\Specialization;
use App\Models\TeamMember;
use RuntimeException;

final class SiteContent
{
    private ?array $site = null;

    public function site(): array
    {
        if ($this->site === null) {
            $data = (new ReviewedContent(config('site.content_path')))->read('site', associative: true);
            if (! is_array($data)) {
                throw new RuntimeException('Invalid site.json.');
            }
            $this->site = $data;
        }

        return $this->site;
    }

    public function specializations(): array
    {
        return Specialization::visible()->get()->map->only([
            'slug', 'title', 'category', 'summary', 'home_summary', 'body', 'scope',
        ])->all();
    }

    public function homepagePartners(): array
    {
        return TeamMember::homepagePartners()->limit(2)->get()->map->only(['name', 'role', 'portrait'])->all();
    }

    public function teamGroups(): array
    {
        $groups = [];
        foreach (TeamGroup::cases() as $group) {
            $groups[$group->value] = ['heading' => $group->heading(), 'people' => []];
        }

        foreach (TeamMember::ordered()->get() as $person) {
            $groups[$person->team_group->value]['people'][] = $person->only(['name', 'role', 'portrait']);
        }

        return $groups;
    }

    public function openVacancies(): array
    {
        return JobOffer::open()->get()->map->only(['title', 'description', 'url'])->all();
    }

    public function publishedArticles(): array
    {
        // PublicationContent's stable date sorting keeps ID order for equal dates.
        return Article::published()->orderBy('id')->get()->map(self::articleData(...))->all();
    }

    public function publishedArticle(string $slug): ?array
    {
        $article = Article::published()->where('slug', $slug)->first();

        return $article === null ? null : self::articleData($article);
    }

    public function sitemapPaths(): array
    {
        return [
            '/', '/o-kancelarii/', '/specjalizacje/',
            ...Specialization::visible()->pluck('slug')->map(static fn (string $slug): string => '/specjalizacje/'.$slug.'/'),
            '/zespol/', '/kariera/', '/kontakt/', '/artykuly/',
            ...Article::published()->orderBy('id')->pluck('slug')->map(static fn (string $slug): string => '/'.$slug.'/'),
        ];
    }

    public static function findBySlug(array $items, string $slug): ?array
    {
        foreach ($items as $item) {
            if (($item['slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }

    public static function articleData(Article $article): array
    {
        return $article->only(['slug', 'title', 'summary', 'category', 'author', 'body_html', 'image', 'image_alt'])
            + ['date' => $article->date?->format('Y-m-d')];
    }
}
