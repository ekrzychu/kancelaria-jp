<?php

namespace App\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

final class PanelAvatar implements AvatarProvider
{
    public function get(Model $record): string
    {
        return '/assets/admin/avatar.svg';
    }
}
