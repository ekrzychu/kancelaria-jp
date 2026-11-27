<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use App\Filament\Resources\Pages\EditContent;

final class EditSpecialization extends EditContent
{
    protected static string $resource = SpecializationResource::class;
    protected static ?string $title = 'Edytuj specjalizację';
}
