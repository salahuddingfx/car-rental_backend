<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewLink extends Model
{
    protected $fillable = [
        'booking_id', 'car_id', 'user_id', 'token', 'used', 'used_at', 'expires_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function car() { return $this->belongsTo(Car::class); }
    public function user() { return $this->belongsTo(User::class); }
}
