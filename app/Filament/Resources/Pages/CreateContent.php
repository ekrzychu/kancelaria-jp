<?php

namespace App\Filament\Resources\Pages;

use App\Actions\ManageContent;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

abstract class CreateContent extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function handleRecordCreation(array $data): Model
    {
        $resource = static::getResource();

        return app(ManageContent::class)->create($resource::getModel(), fn (): array => $resource::contentData($data));
    }
}
