<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\Pages\CreateContent;

final class CreateTeamMember extends CreateContent
{
    protected static string $resource = TeamMemberResource::class;
    protected static ?string $title = 'Dodaj profil';
}
