<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\ManageAccount;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Dodaj użytkownika';

    protected static bool $canCreateAnother = false;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function handleRecordCreation(#[SensitiveParameter] array $data): Model
    {
        return app(ManageAccount::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl();
    }
}
