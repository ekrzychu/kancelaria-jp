<?php

namespace App\Filament\Resources\JobOfferResource\Pages;

use App\Filament\Resources\JobOfferResource;
use App\Filament\Resources\Pages\CreateContent;
use Filament\Actions\Action;

final class CreateJobOffer extends CreateContent
{
    protected static string $resource = JobOfferResource::class;
    protected static ?string $title = 'Dodaj ofertę';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Zapisz szkic');
    }
}
