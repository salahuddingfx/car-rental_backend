<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id && $user->role === 'admin';
    }
}
