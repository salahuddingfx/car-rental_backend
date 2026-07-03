<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Events\PaymentReceived;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initiate(Request $request, Booking $booking)
    {
        if ($booking->payment_status === 'paid') {
            abort(422, 'This booking is already paid.');
        }

        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'method' => 'required|in:bkash,nagad,cod,bank_transfer',
            'sender_number' => 'required_if:method,bkash,nagad|string|max:20',
            'transaction_id' => 'required_if:method,bkash,nagad|string|max:100',
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'amount' => $booking->total_price,
            'currency' => 'BDT',
            'method' => $validated['method'],
            'status' => 'pending',
            'transaction_id' => $validated['transaction_id'] ?? null,
            'sender_number' => $validated['sender_number'] ?? null,
        ]);

        $booking->update(['payment_status' => 'unpaid']);

        return response()->json([
            'message' => 'Payment submitted. Awaiting verification.',
            'payment' => $payment,
        ], 201);
    }

    public function myPayments(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('booking')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payments);
    }

    public function show(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

        return response()->json($payment->load('booking'));
    }

    // Admin: verify payment
    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:completed,failed',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'verified_at' => now(),
        ]);

        if ($request->status === 'completed') {
            $payment->booking->update(['payment_status' => 'paid', 'payment_id' => $payment->id]);
            PaymentReceived::dispatch($payment->load(['booking.car', 'user']));
        } elseif ($request->status === 'failed') {
            $payment->booking->update(['payment_status' => 'unpaid']);
        }

        return response()->json(['message' => 'Payment ' . $request->status, 'payment' => $payment]);
    }

    // Admin: list pending payments
    public function pending(Request $request)
    {
        $payments = Payment::where('status', 'pending')
            ->with(['booking', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payments);
    }

    // Admin: list all payments
    public function all(Request $request)
    {
        $payments = Payment::with(['booking', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payments);
    }
}
