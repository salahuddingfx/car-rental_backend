<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type', 'path', 'method', 'status_code', 'user_id',
        'ip_address', 'user_agent', 'referer', 'device_type',
        'browser', 'os', 'country', 'response_time_ms', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'user_id' => 'integer',
    ];

    public $timestamps = false;
}
