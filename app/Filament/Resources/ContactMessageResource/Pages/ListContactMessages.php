<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

final class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;
    protected static ?string $title = 'Wiadomości';

    // Search terms may contain personal information and should stay out of URLs.
    public $tableSearch = '';
}
