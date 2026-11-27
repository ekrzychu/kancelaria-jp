<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Actions\ManageContent;
use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\Pages\EditContent;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

final class EditTeamMember extends EditContent
{
    protected static string $resource = TeamMemberResource::class;
    protected static ?string $title = 'Edytuj profil zespołu';

    #[Locked]
    public string $originalPortrait;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalPortrait = $this->portraitHash($this->record);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $current = app(ManageContent::class)->change($record, 'update', function (TeamMember $current) use ($data): void {
            if (! hash_equals($this->originalPortrait, $this->portraitHash($current))) {
                throw ValidationException::withMessages(['data.portrait_media_id' => 'Zdjęcie zmieniło się od otwarcia formularza. Odśwież stronę.']);
            }
            $current->fill(TeamMemberResource::contentData($data, $current));
            TeamMemberResource::savePortrait($current, $data);
        });
        $record->setRawAttributes($current->getAttributes(), sync: true);
        $this->originalPortrait = $this->portraitHash($current);

        return $record;
    }

    private function portraitHash(TeamMember $record): string
    {
        return hash('sha256', serialize([$record->portrait, $record->portrait_media_id]));
    }
}
