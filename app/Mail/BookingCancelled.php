<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your booking #{$this->booking->booking_ref} has been cancelled",
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
        $booking = $this->booking;
        $car = $booking->car;
        $user = $booking->user;
        $appName = config('app.name', 'Apex Ride');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: 'Helvetica Neue', Arial, sans-serif; background: #f9fafb; padding: 40px 20px; color: #1f2937;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div style="background: #dc2626; padding: 24px; text-align: center;">
                <h1 style="color: #ffffff; font-size: 20px; margin: 0;">{$appName}</h1>
            </div>
            <div style="padding: 32px;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 56px; height: 56px; background: #fee2e2; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px;">&#10005;</div>
                </div>
                <h2 style="text-align: center; font-size: 22px; margin: 0 0 8px;">Booking Cancelled</h2>
                <p style="text-align: center; color: #6b7280; font-size: 14px; margin: 0 0 24px;">Hi {$user->name}, your car rental booking has been cancelled.</p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Booking Ref</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$booking->booking_ref}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Vehicle</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$car->brand} {$car->name}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Pickup Date</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$booking->pickup_date}</td></tr>
                    <tr><td style="padding: 8px 0; color: #6b7280; font-size: 13px;">Return Date</td><td style="padding: 8px 0; font-weight: 600; text-align: right; font-size: 13px;">{$booking->return_date}</td></tr>
                </table>

                <p style="text-align: center; color: #6b7280; font-size: 12px; margin: 0;">If this was a mistake, you can rebook anytime on {$appName}.</p>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }
}
