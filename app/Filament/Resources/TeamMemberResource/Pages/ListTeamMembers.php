<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Resources\TeamMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListTeamMembers extends ListRecords
{
    protected static string $resource = TeamMemberResource::class;
    protected static ?string $title = 'Zespół';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Dodaj profil')];
    }
}
