<?php

namespace App\Policies;

use App\Enums\ContentSection;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->canAccessSection(ContentSection::Articles) ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Article $article): bool
    {
        return true;
    }

    public function delete(User $user, Article $article): bool
    {
        return true;
    }

    public function restore(User $user, Article $article): bool
    {
        return true;
    }

    public function publish(User $user, Article $article): bool
    {
        return true;
    }
}
