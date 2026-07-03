<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'group', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }
}
