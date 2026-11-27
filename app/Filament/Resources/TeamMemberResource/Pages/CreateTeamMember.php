<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Actions\ManageContent;
use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\Pages\CreateContent;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;

final class CreateTeamMember extends CreateContent
{
    protected static string $resource = TeamMemberResource::class;
    protected static ?string $title = 'Dodaj profil';

    protected function handleRecordCreation(array $data): Model
    {
        return app(ManageContent::class)->create(TeamMember::class,
            fn (): array => TeamMemberResource::contentData($data),
            fn (TeamMember $record) => TeamMemberResource::savePortrait($record, $data));
    }
}
