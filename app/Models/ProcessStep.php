<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['step', 'title', 'description', 'icon', 'sort_order'];
}
