<?php

namespace App\Media;

use App\Enums\ContentSection;

enum MediaPurpose: string
{
    case Article = 'article';
    case Portrait = 'portrait';

    public function section(): ContentSection
    {
        return match ($this) {
            self::Article => ContentSection::Articles,
            self::Portrait => ContentSection::Team,
        };
    }
}
