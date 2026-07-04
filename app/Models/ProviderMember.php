<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id', 'user_id', 'role',
        'license_number', 'license_expiry', 'license_country', 'license_image', 'license_verified',
        'status', 'suspension_reason',
        'total_trips', 'avg_rating', 'is_available',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'license_verified' => 'boolean',
        'is_available' => 'boolean',
        'avg_rating' => 'decimal:2',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBookings()
    {
        return $this->hasMany(Booking::class, 'assigned_driver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('status', 'active');
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function isAvailable(): bool
    {
        return $this->is_available && $this->status === 'active';
    }

    public function hasValidLicense(): bool
    {
        return $this->license_verified
            && $this->license_expiry
            && $this->license_expiry->isFuture();
    }
}
