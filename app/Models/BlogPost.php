<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['slug', 'title', 'excerpt', 'content', 'category', 'date', 'read_time', 'image', 'is_published'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_published' => 'boolean'];
    }
}
