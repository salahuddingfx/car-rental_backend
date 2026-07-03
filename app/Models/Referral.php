<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = ['referrer_id', 'referee_id', 'code', 'status', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function referrer() { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referee() { return $this->belongsTo(User::class, 'referee_id'); }
}
