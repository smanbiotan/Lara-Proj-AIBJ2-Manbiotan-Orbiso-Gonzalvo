<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\ChatbotConversation;
use App\Services\AutomationService;
use App\Services\ChatFallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * POST /api/chat — the browser talks ONLY to Laravel. Laravel forwards to n8n (Gemini) with the
 * server-side secret, stores the conversation, and falls back to database facts if n8n is down.
 */
class ChatController extends Controller
{
    public function send(Request $request, AutomationService $automation, ChatFallbackService $fallback)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'session_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\-]{16,64}$/'],
        ]);

        $conversation = ChatbotConversation::firstOrCreate(
            ['session_token' => $data['session_id'] ?? (string) Str::uuid()],
            ['status' => 'open']
        );

        $history = $conversation->messages()->latest('id')->take(10)->get()->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])->values()->all();

        $message = trim(strip_tags($data['message']));
        $conversation->messages()->create(['role' => 'user', 'content' => $message, 'source' => 'customer']);

        $reply = $automation->chat($message, $conversation->session_token, $history);
        $mode = 'ai';
        if ($reply === null) {
            $reply = $fallback->reply($message);
            $mode = 'fallback';
        }

        $conversation->messages()->create(['role' => 'assistant', 'content' => $reply, 'source' => $mode === 'ai' ? 'n8n' : 'fallback']);
        $conversation->update(['last_activity_at' => now()]);

        return response()->json([
            'reply' => $reply,
            'mode' => $mode,
            'session_id' => $conversation->session_token,
        ]);
    }
}
