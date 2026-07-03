<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Mail\BookingCancelled as BookingCancelledMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCancelledEmail implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        if ($booking->user && $booking->user->email) {
            Mail::to($booking->user->email)->send(new BookingCancelledMail($booking));
        }
    }
}
