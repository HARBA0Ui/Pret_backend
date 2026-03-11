<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function myConversation(Request $request)
    {
        $user = $request->user();
        $conversation = $this->chatService->ensureConversationForEmployee((string) $user->id);

        return response()->json([
            'conversation' => $this->chatService->mapConversation($conversation),
        ]);
    }

    public function myMessages(Request $request)
    {
        $user = $request->user();
        $conversation = $this->chatService->ensureConversationForEmployee((string) $user->id);

        $limit = (int) $request->query('limit', 200);
        $limit = max(1, min($limit, 500));

        $messages = $conversation->messages()
            ->orderBy('sentAt', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->chatService->mapMessage($m))
            ->values();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string|min:1|max:2000',
        ]);

        $user = $request->user();
        $conversation = $this->chatService->ensureConversationForEmployee((string) $user->id);

        $message = $this->chatService->createMessage($conversation, $user, trim($data['content']));

        return response()->json([
            'message' => $this->chatService->mapMessage($message),
        ], 201);
    }

    public function deleteConversation(Request $request)
    {
        $user = $request->user();

        $conversation = ChatConversation::query()
            ->where('employeeId', (string) $user->id)
            ->first();

        if ($conversation) {
            $this->chatService->deleteConversation($conversation);
        }

        return response()->noContent();
    }
}
