<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'car_id', 'booking_id', 'name', 'avatar', 'rating',
        'car_condition', 'driver_rating', 'value_rating', 'cleanliness',
        'text', 'photos', 'host_response', 'is_verified', 'helpful_count',
        'source', 'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'photos' => 'array',
            'is_verified' => 'boolean',
            'car_condition' => 'decimal:1',
            'driver_rating' => 'decimal:1',
            'value_rating' => 'decimal:1',
            'cleanliness' => 'decimal:1',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function car() { return $this->belongsTo(Car::class); }
    public function booking() { return $this->belongsTo(Booking::class); }
}
