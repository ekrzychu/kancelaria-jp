<?php

namespace App\Policies;

use App\Enums\ContentSection;
use App\Models\ContactMessage;
use App\Models\User;

final class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessSection(ContentSection::Messages);
    }

    public function view(User $user, ContactMessage $record): bool
    {
        return $this->viewAny($user);
    }

    public function manage(User $user, ContactMessage $record): bool
    {
        return $this->view($user, $record);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ContactMessage $record): bool
    {
        return false;
    }

    public function delete(User $user, ContactMessage $record): bool
    {
        return false;
    }
}
