<?php

namespace App\Models;

use App\Enums\ContentSection;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'session_version'])]
class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_active;
    }

    public function isActiveAdministrator(): bool
    {
        return $this->is_active && $this->is_admin;
    }

    public function canAccessSection(ContentSection $section): bool
    {
        return $this->is_active && ($this->is_admin || in_array($section->value, $this->permissions ?? [], true));
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'last_login_at' => 'datetime',
            'session_version' => 'integer',
        ];
    }
}
