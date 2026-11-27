<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use App\Filament\Resources\Pages\CreateContent;

final class CreateSpecialization extends CreateContent
{
    protected static string $resource = SpecializationResource::class;
    protected static ?string $title = 'Dodaj specjalizację';
}
