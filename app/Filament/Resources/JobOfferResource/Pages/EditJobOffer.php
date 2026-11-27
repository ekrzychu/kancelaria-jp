<?php

namespace App\Filament\Resources\JobOfferResource\Pages;

use App\Filament\Resources\JobOfferResource;
use App\Filament\Resources\Pages\EditContent;
use App\Models\JobOffer;
use Illuminate\Validation\ValidationException;

final class EditJobOffer extends EditContent
{
    protected static string $resource = JobOfferResource::class;
    protected static ?string $title = 'Edytuj ofertę pracy';

    protected function getHeaderActions(): array
    {
        return JobOfferResource::publicationActions(function (JobOffer $record): array {
            try {
                return JobOfferResource::contentData($this->form->getState(), $record);
            } catch (ValidationException $exception) {
                $this->unmountAction(cancelParentActions: false);

                throw $exception;
            }
        });
    }
}
