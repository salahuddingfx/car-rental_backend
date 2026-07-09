<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsLocation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'booking_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'accuracy',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
            'accuracy' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
