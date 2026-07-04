<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'avatar',
        'phone', 'license_number', 'license_expiry', 'license_country', 'license_image', 'license_verified',
        'loyalty_points', 'referral_code', 'tier',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'license_verified' => 'boolean',
            'loyalty_points' => 'integer',
        ];
    }

    public function cars() { return $this->hasMany(Car::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function loyaltyPoints() { return $this->hasMany(LoyaltyPoint::class); }
    public function referralsMade() { return $this->hasMany(Referral::class, 'referrer_id'); }
    public function referralsReceived() { return $this->hasMany(Referral::class, 'referee_id'); }

    public function provider() { return $this->hasOne(Provider::class); }
    public function providerMemberships() { return $this->hasMany(ProviderMember::class); }

    public function isProvider(): bool
    {
        return $this->provider()->exists();
    }

    public function isDriverMember(): bool
    {
        return $this->providerMemberships()->where('role', 'driver')->exists();
    }
}
