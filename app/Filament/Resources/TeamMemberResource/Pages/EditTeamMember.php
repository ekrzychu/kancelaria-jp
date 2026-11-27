<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\Pages\EditContent;

final class EditTeamMember extends EditContent
{
    protected static string $resource = TeamMemberResource::class;
    protected static ?string $title = 'Edytuj profil zespołu';
}
