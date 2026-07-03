<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register event listeners explicitly (sync driver for shared hosting)
        Event::listen(
            \App\Events\BookingConfirmed::class,
            \App\Listeners\SendBookingConfirmedEmail::class
        );

        Event::listen(
            \App\Events\BookingCancelled::class,
            \App\Listeners\SendBookingCancelledEmail::class
        );

        Event::listen(
            \App\Events\PaymentReceived::class,
            \App\Listeners\SendPaymentReceivedEmail::class
        );
    }
}
