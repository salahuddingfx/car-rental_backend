<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Provider $provider): bool
    {
        return $provider->isVerified() && $provider->is_active;
    }

    public function create(User $user): bool
    {
        return !$user->provider()->exists();
    }

    public function update(User $user, Provider $provider): bool
    {
        return $user->id === $provider->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $user->id === $provider->user_id || $user->role === 'admin';
    }

    public function manageMembers(User $user, Provider $provider): bool
    {
        return ($user->id === $provider->user_id && $provider->canAddDrivers()) || $user->role === 'admin';
    }

    public function assignDriver(User $user, Provider $provider): bool
    {
        return $user->id === $provider->user_id || $user->role === 'admin';
    }
}
