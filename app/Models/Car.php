<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'brand', 'category', 'price', 'seats', 'transmission', 'fuel',
        'power', 'speed', 'description', 'features', 'image', 'images', 'location',
        'latitude', 'longitude', 'rating', 'reviews_count', 'is_available', 'year', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'images' => 'array',
            'price' => 'decimal:2',
            'rating' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_available' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function reviews() { return $this->hasMany(Review::class); }
}
