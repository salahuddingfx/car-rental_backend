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
        'provider_id', 'assigned_driver_id', 'is_premium', 'premium_expires_at', 'premium_priority',
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
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function provider() { return $this->belongsTo(Provider::class); }
    public function assignedDriver() { return $this->belongsTo(ProviderMember::class, 'assigned_driver_id'); }
    public function premiumOrders() { return $this->hasMany(PremiumOrder::class); }
}
