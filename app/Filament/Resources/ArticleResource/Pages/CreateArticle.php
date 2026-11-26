<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Content\ArticleEditing;
use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

final class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected static ?string $title = 'Dodaj artykuł';

    protected static bool $canCreateAnother = false;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function handleRecordCreation(array $data): Model
    {
        Gate::authorize('create', Article::class);

        return Article::create(Arr::only($data, ['slug', 'title', 'category', 'author', 'date', 'summary'])
            + ['body_html' => app(ArticleEditing::class)->saveBody(null, $data)]);
    }
}
