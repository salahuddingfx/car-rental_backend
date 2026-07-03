<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id', 'user_id', 'guest_name', 'guest_email',
        'guest_phone', 'guest_country', 'guest_address',
        'status', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function unreadCount()
    {
        return $this->messages()->where('is_read', false)->where('sender_type', '!=', 'admin')->count();
    }
}
