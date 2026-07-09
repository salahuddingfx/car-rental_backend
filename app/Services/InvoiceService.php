<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\View;

class InvoiceService
{
    public function generatePdf(Booking $booking): string
    {
        $booking->load(['car', 'user', 'payment', 'provider']);

        $html = View::make('invoices.booking', [
            'booking' => $booking,
            'appName' => config('app.name', 'Apex Ride'),
            'appUrl' => config('app.url'),
        ])->render();

        // Use a simple HTML to PDF approach
        // For production, use dompdf or wkhtmltopdf
        return $html;
    }

    public function generateAndStore(Booking $booking): string
    {
        $html = $this->generatePdf($booking);
        $filename = "invoice-{$booking->booking_ref}.html";
        $path = storage_path("app/invoices/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $html);
        return $path;
    }
}
