<?php

namespace App\Policies;

use App\Enums\ContentSection;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class SectionContentPolicy
{
    protected const ContentSection SECTION = ContentSection::Specializations;

    public function viewAny(User $user): bool
    {
        return $user->canAccessSection(static::SECTION);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->viewAny($user) && ! $record->trashed();
    }

    public function reorder(User $user, Model $record): bool
    {
        return $this->update($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->update($user, $record);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->viewAny($user) && $record->trashed();
    }
}
