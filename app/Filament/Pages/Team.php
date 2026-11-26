<?php

namespace App\Filament\Pages;

use App\Enums\ContentSection;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Team extends SectionPlaceholder
{
    protected const ContentSection SECTION = ContentSection::Team;

    protected static ?string $title = 'Zespół';

    protected static ?string $slug = 'zespol';

    protected static ?int $navigationSort = 3;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;
}
