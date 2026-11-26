<?php

namespace App\Filament\Pages;

use App\Enums\ContentSection;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Specializations extends SectionPlaceholder
{
    protected const ContentSection SECTION = ContentSection::Specializations;

    protected static ?string $title = 'Specjalizacje';

    protected static ?string $slug = 'specjalizacje';

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
