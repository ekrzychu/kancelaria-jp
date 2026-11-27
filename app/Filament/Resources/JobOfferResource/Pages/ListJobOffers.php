<?php

namespace App\Filament\Resources\JobOfferResource\Pages;

use App\Filament\Resources\JobOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListJobOffers extends ListRecords
{
    protected static string $resource = JobOfferResource::class;
    protected static ?string $title = 'Kariera';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Dodaj ofertę')];
    }
}
