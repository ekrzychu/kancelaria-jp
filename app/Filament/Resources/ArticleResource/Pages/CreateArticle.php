<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Content\ArticleEditing;
use App\Content\ArticleStatus;
use App\Content\ContentWriteLock;
use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\Pages\PublishesOnCreate;
use App\Media\MediaReferences;
use App\Models\Article;
use App\Support\PublicUrl;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateArticle extends CreateRecord
{
    use PublishesOnCreate;

    protected static string $resource = ArticleResource::class;

    protected static ?string $title = 'Dodaj artykuł';

    protected static bool $canCreateAnother = false;

    protected Width|string|null $maxContentWidth = Width::ScreenTwoExtraLarge;

    protected function getRedirectUrl(): string
    {
        return ArticleResource::getUrl();
    }

    protected function handleRecordCreation(array $data): Model
    {
        Gate::authorize('create', Article::class);

        return ContentWriteLock::transaction(DB::connection(), function () use ($data): Article {
            $slug = auth()->user()->isActiveAdministrator() ? ($data['slug'] ?? null) : null;
            if (filled($slug) && Article::withTrashed()->where('slug', $slug)->exists()) {
                throw ValidationException::withMessages(['data.slug' => 'Ten adres jest zajęty. Wybierz inny.']);
            }
            $article = Article::create(Arr::only($data, ['title', 'category', 'author', 'date', 'summary']) + [
                'slug' => filled($slug) ? $slug : PublicUrl::availableSlug($data['title'], Article::class, PublicUrl::RESERVED_SLUGS),
                'body_html' => app(ArticleEditing::class)->saveBody(null, $data),
            ]);
            app(MediaReferences::class)->saveArticle($article, $data + ['image_blocks' => []]);
            if ($this->publishing) {
                Gate::authorize('publish', $article);
                $article->status = ArticleStatus::Published;
                $article->date ??= now('Europe/Warsaw')->toDateString();
            }
            $article->save();

            return $article;
        }, fn () => ValidationException::withMessages(['data.title' => 'Trwa zapis treści. Spróbuj ponownie za chwilę.']));
    }
}
