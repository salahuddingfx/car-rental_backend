<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment received for booking #{$this->payment->booking->booking_ref}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $payment = $this->payment;
        $booking = $payment->booking;
        $user = $payment->user;
        $appName = config('app.name', 'Apex Ride');
        $method = ucfirst($payment->method);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: 'Helvetica Neue', Arial, sans-serif; background: #f9fafb; padding: 40px 20px; color: #1f2937;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div style="background: #16a34a; padding: 24px; text-align: center;">
                <h1 style="color: #ffffff; font-size: 20px; margin: 0;">{$appName}</h1>
            </div>
            <div style="padding: 32px;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 56px; height: 56px; background: #dcfce7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px;">&#2547;</div>
                </div>
                <h2 style="text-align: center; font-size: 22px; margin: 0 0 8px;">Payment Confirmed!</h2>
                <p style="text-align: center; color: #6b7280; font-size: 14px; margin: 0 0 24px;">Hi {$user->name}, we've received your payment.</p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Amount</td><td style="padding: 8px 0; font-weight: 700; text-align: right; font-size: 15px; color: #16a34a;">&#2547;{$payment->amount}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Method</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$method}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Transaction ID</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px; font-family: monospace;">{$payment->transaction_id}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Booking Ref</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$booking->booking_ref}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Vehicle</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$booking->car->brand} {$booking->car->name}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Status</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px; color: #16a34a;">Confirmed</td></tr>
                </table>

                <p style="text-align: center; color: #6b7280; font-size: 12px; margin: 0;">Your booking is now fully confirmed. See you on the pickup date!</p>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }
}
