<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['year', 'title', 'description', 'icon', 'type', 'sort_order'];
}
