<?php

namespace App\Filament\Pages;

use App\Enums\ContentSection;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Career extends SectionPlaceholder
{
    protected const ContentSection SECTION = ContentSection::Career;

    protected static ?string $title = 'Kariera';

    protected static ?string $slug = 'kariera';

    protected static ?int $navigationSort = 4;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBriefcase;
}
