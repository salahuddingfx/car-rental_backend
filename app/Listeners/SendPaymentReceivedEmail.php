<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Mail\PaymentReceived as PaymentReceivedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceivedEmail implements ShouldQueue
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        if ($payment->user && $payment->user->email) {
            Mail::to($payment->user->email)->send(new PaymentReceivedMail($payment));
        }
    }
}
