<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id', 'document_type', 'document_number',
        'document_image', 'expires_at',
        'status', 'admin_notes', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'verified_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
