<?php

namespace App\Filament\Pages;

use App\Enums\ContentSection;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Messages extends SectionPlaceholder
{
    protected const ContentSection SECTION = ContentSection::Messages;

    protected static ?string $title = 'Wiadomości';

    protected static ?string $slug = 'wiadomosci';

    protected static ?int $navigationSort = 5;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedEnvelope;
}
