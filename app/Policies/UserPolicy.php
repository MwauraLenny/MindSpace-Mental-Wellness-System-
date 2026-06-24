<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id || $currentUser->role === 'admin';
    }

    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id || $currentUser->role === 'admin';
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id || $currentUser->role === 'admin';
    }
}
