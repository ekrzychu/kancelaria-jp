<?php

namespace App\Support;

use App\Models\MediaImage;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class PanelAvatar implements AvatarProvider
{
    public function get(Model $record): string
    {
        if ($record->avatar_media_id) {
            return MediaImage::url($record->avatar_media_id, private: true);
        }

        $initials = Str::of($record->name)->trim()->explode(' ')->filter()->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');

        return 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#eadce1"/><text x="32" y="33" dominant-baseline="middle"'
            .' text-anchor="middle" font-family="system-ui" font-size="24" fill="#341426">'.e($initials).'</text></svg>');
    }
}
