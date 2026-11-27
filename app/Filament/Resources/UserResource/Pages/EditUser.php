<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\ManageAccount;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Edytuj użytkownika';

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    public function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Zapisz zmiany')->submit(null)->action('save')->requiresConfirmation()
            ->modalDescription('Zmiany uprawnień lub adresu e-mail zakończą bieżące sesje użytkownika.');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ManageAccount::class)->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl();
    }
}
