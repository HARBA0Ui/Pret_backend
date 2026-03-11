<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\ChatService;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        $query = ChatConversation::with('employee')
            ->orderBy('updatedAt', 'desc');

        $conversations = $query->limit($perPage)->get();

        $payload = $conversations->map(function ($conversation) {
            return $this->chatService->mapConversation($conversation, true);
        })->values();

        return response()->json([
            'conversations' => $payload,
        ]);
    }

    public function show(string $id)
    {
        $conversation = ChatConversation::with('employee')->findOrFail($id);

        $messages = $conversation->messages()
            ->orderBy('sentAt', 'asc')
            ->get()
            ->map(fn($m) => $this->chatService->mapMessage($m))
            ->values();

        return response()->json([
            'conversation' => $this->chatService->mapConversation($conversation, true),
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, string $id)
    {
        $data = $request->validate([
            'content' => 'required|string|min:1|max:2000',
        ]);

        $conversation = ChatConversation::findOrFail($id);
        $message = $this->chatService->createMessage($conversation, $request->user(), trim($data['content']));

        return response()->json([
            'message' => $this->chatService->mapMessage($message),
        ], 201);
    }

    public function destroy(string $id)
    {
        $conversation = ChatConversation::findOrFail($id);
        $this->chatService->deleteConversation($conversation);

        return response()->noContent();
    }
}
