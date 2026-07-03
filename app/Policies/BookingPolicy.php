<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->role === 'admin';
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }
}
