<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'cta_text', 'cta_link', 'image', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
