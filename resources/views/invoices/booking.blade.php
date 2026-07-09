<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $booking->booking_ref }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1f2937; background: #fff; padding: 40px; }
        .invoice-box { max-width: 800px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: #2563eb; color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .content { padding: 30px; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
        .invoice-info div { flex: 1; }
        .invoice-info h3 { font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 5px; }
        .invoice-info p { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f9fafb; text-align: left; padding: 12px; font-size: 12px; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .total-row { background: #f9fafb; font-weight: 700; }
        .total-row td { border-bottom: none; padding: 15px 12px; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #6b7280; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-unpaid { background: #fef2f2; color: #991b1b; }
        .badge-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>{{ $appName }}</h1>
            <p>Invoice / বিল</p>
        </div>
        <div class="content">
            <div class="invoice-info">
                <div>
                    <h3>Invoice To</h3>
                    <p><strong>{{ $booking->user->name ?? 'Guest' }}</strong></p>
                    <p>{{ $booking->user->email ?? '' }}</p>
                    <p>{{ $booking->user->phone ?? '' }}</p>
                </div>
                <div>
                    <h3>Invoice Details</h3>
                    <p><strong>Invoice #:</strong> INV-{{ $booking->booking_ref }}</p>
                    <p><strong>Date:</strong> {{ $booking->created_at->format('d M Y') }}</p>
                    <p><strong>Booking Ref:</strong> {{ $booking->booking_ref }}</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Details</th>
                        <th style="text-align: right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $booking->car->brand }} {{ $booking->car->name }}</strong>
                            <br><small style="color: #6b7280">Car Rental</small>
                        </td>
                        <td>
                            Pickup: {{ \Carbon\Carbon::parse($booking->pickup_date)->format('d M Y') }}<br>
                            Return: {{ \Carbon\Carbon::parse($booking->return_date)->format('d M Y') }}<br>
                            Duration: {{ $booking->total_days }} day(s)
                        </td>
                        <td style="text-align: right">BDT {{ number_format($booking->total_price * 0.80, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Trip Fee (12%)</td>
                        <td>Service charge</td>
                        <td style="text-align: right">BDT {{ number_format($booking->total_price * 0.12, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax (8%)</td>
                        <td>VAT</td>
                        <td style="text-align: right">BDT {{ number_format($booking->total_price * 0.08, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td style="text-align: right"><strong>BDT {{ number_format($booking->total_price, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            @if($booking->payment)
            <div style="margin-bottom: 20px;">
                <p><strong>Payment Method:</strong> {{ ucfirst($booking->payment->method) }}</p>
                <p><strong>Payment Status:</strong>
                    <span class="badge badge-{{ $booking->payment_status === 'paid' ? 'paid' : ($booking->payment_status === 'unpaid' ? 'unpaid' : 'pending') }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </p>
                @if($booking->payment->transaction_id)
                <p><strong>Transaction ID:</strong> {{ $booking->payment->transaction_id }}</p>
                @endif
            </div>
            @endif
        </div>
        <div class="footer">
            <p>Thank you for choosing {{ $appName }}! | {{ $appUrl }}</p>
            <p style="margin-top: 5px;">This is a computer-generated invoice.</p>
        </div>
    </div>
</body>
</html>
