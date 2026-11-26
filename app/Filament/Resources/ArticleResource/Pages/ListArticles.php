<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected static ?string $title = 'Artykuły';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Dodaj artykuł')];
    }
}
