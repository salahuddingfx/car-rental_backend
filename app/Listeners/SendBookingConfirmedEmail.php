<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Mail\BookingConfirmed as BookingConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmedEmail implements ShouldQueue
{
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;
        if ($booking->user && $booking->user->email) {
            Mail::to($booking->user->email)->send(new BookingConfirmedMail($booking));
        }
    }
}
