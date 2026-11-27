<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSpecializations extends ListRecords
{
    protected static string $resource = SpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Dodaj specjalizację')];
    }
}
