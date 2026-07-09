<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Request $request, Booking $booking)
    {
        // User can only download their own invoices
        if ($request->user()->id !== $booking->user_id && $request->user()->role !== 'admin') {
            abort(403);
        }

        $service = new InvoiceService();
        $html = $service->generatePdf($booking->load(['car', 'user', 'payment']));

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="invoice-{$booking->booking_ref}.html"');
    }

    public function pdf(Request $request, Booking $booking)
    {
        if ($request->user()->id !== $booking->user_id && $request->user()->role !== 'admin') {
            abort(403);
        }

        $service = new InvoiceService();
        $path = $service->generateAndStore($booking->load(['car', 'user', 'payment']));

        return response()->download($path, "invoice-{$booking->booking_ref}.html");
    }
}
