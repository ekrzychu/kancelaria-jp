<?php

namespace App\Filament\Pages;

use App\Enums\ContentSection;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

abstract class SectionPlaceholder extends Page
{
    protected const ContentSection SECTION = ContentSection::Specializations;

    protected string $view = 'filament.pages.section-placeholder';

    public static function canAccess(): bool
    {
        return Gate::allows('access-content', static::SECTION);
    }
}
