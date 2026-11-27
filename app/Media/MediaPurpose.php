<?php

namespace App\Media;

use App\Enums\ContentSection;
use App\Models\User;

enum MediaPurpose: string
{
    case Article = 'article';
    case Portrait = 'portrait';
    case Avatar = 'avatar';

    public function allowed(User $user): bool
    {
        return match ($this) {
            self::Article => $user->canAccessSection(ContentSection::Articles),
            self::Portrait => $user->canAccessSection(ContentSection::Team),
            self::Avatar => $user->is_active,
        };
    }
}
