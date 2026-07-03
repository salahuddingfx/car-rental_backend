<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|string|max:100',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'guest_country' => 'nullable|string|max:100',
            'guest_address' => 'nullable|string|max:500',
        ]);

        $chat = Chat::where('guest_id', $validated['guest_id'])
            ->where('status', 'open')
            ->first();

        if (!$chat) {
            $chat = Chat::create($validated);
        }

        return response()->json($chat->load('messages'));
    }

    public function sendMessage(Request $request, Chat $chat)
    {
        // CRITICAL: Only allow guest sender_type on public endpoint to prevent impersonation
        $validated = $request->validate([
            'sender_type' => 'required|in:guest',
            'sender_id' => 'nullable|string|max:100',
            'sender_name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $message = $chat->messages()->create([
            ...$validated,
            'is_read' => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        return response()->json($message, 201);
    }

    public function messages(Request $request, Chat $chat)
    {
        // Rate limit message reading
        if (RateLimiter::tooManyAttempts('chat-messages:' . $request->ip(), 30)) {
            abort(429, 'Too many requests.');
        }
        RateLimiter::hit('chat-messages:' . $request->ip(), 60);

        return response()->json($chat->messages()->orderBy('created_at')->get());
    }

    public function byGuest(Request $request)
    {
        $request->validate(['guest_id' => 'required|string|max:100']);

        if (RateLimiter::tooManyAttempts('chat-guest:' . $request->ip(), 20)) {
            abort(429, 'Too many requests.');
        }
        RateLimiter::hit('chat-guest:' . $request->ip(), 60);

        $chat = Chat::where('guest_id', $request->guest_id)
            ->where('status', 'open')
            ->with('messages')
            ->first();

        if (!$chat) {
            return response()->json(null);
        }

        return response()->json($chat);
    }

    public function markRead(Chat $chat)
    {
        $chat->messages()
            ->where('sender_type', '!=', 'admin')
            ->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    public function adminIndex(Request $request)
    {
        $chats = Chat::with(['messages' => function ($q) {
            $q->latest()->limit(1);
        }])
        ->orderByDesc('last_message_at')
        ->paginate(20);

        return response()->json($chats);
    }

    public function adminReply(Request $request, Chat $chat)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $message = $chat->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'sender_name' => $request->user()->name,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        return response()->json($message, 201);
    }

    public function adminClose(Chat $chat)
    {
        $chat->update(['status' => 'closed']);
        return response()->json($chat);
    }

    public function adminUnread()
    {
        $count = ChatMessage::where('sender_type', '!=', 'admin')
            ->where('is_read', false)
            ->count();
        return response()->json(['unread' => $count]);
    }
}
