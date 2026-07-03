<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_ref', 'car_id', 'user_id', 'pickup_date', 'return_date',
        'total_days', 'total_price', 'status', 'driver_info', 'payment_status', 'payment_id',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'driver_info' => 'array',
        ];
    }

    public function car() { return $this->belongsTo(Car::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
}
