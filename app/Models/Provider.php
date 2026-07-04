<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'type', 'description', 'logo', 'cover_image',
        'contact_email', 'contact_phone', 'website',
        'address', 'city', 'state', 'country', 'latitude', 'longitude',
        'rating', 'total_reviews', 'total_bookings', 'total_cars',
        'verification_status', 'rejection_reason', 'verified_at',
        'is_active', 'is_featured', 'commission_rate',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'verified_at' => 'datetime',
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function members()
    {
        return $this->hasMany(ProviderMember::class);
    }

    public function drivers()
    {
        return $this->hasMany(ProviderMember::class)->where('role', 'driver');
    }

    public function verifications()
    {
        return $this->hasMany(ProviderVerification::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(ProviderMember::class)->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNearby($query, $lat, $lng, $radiusKm = 50)
    {
        return $query->selectRaw("*, (
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isAgency(): bool
    {
        return $this->type === 'agency';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function canAddDrivers(): bool
    {
        return in_array($this->type, ['agency', 'company']);
    }
}
