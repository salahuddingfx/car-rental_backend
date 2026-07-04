<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_ref', 'car_id', 'guest_name', 'guest_email', 'guest_phone',
        'guest_country', 'guest_location', 'license_number', 'license_expiry',
        'pickup_date', 'return_date', 'total_days', 'total_price', 'status',
        'driver_info', 'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'driver_info' => 'array',
            'pickup_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
