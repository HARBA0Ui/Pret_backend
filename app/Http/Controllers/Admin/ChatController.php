<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request)
    {
        $employeeSearch = trim((string) $request->get('employee_search', ''));

        $conversations = ChatConversation::with('employee')
            ->orderBy('updatedAt', 'desc')
            ->get();

        if ($employeeSearch !== '') {
            $needle = mb_strtolower($employeeSearch, 'UTF-8');
            $conversations = $conversations->filter(function ($c) use ($needle) {
                $name = mb_strtolower((string) ($c->employee->name ?? ''), 'UTF-8');
                $email = mb_strtolower((string) ($c->employee->email ?? ''), 'UTF-8');
                $matricule = mb_strtolower((string) ($c->employee->matricule ?? ''), 'UTF-8');

                return str_contains($name, $needle)
                    || str_contains($email, $needle)
                    || str_contains($matricule, $needle);
            });
        }

        return view('admin.chat.index', [
            'conversations' => $conversations,
            'employeeSearch' => $employeeSearch,
        ]);
    }

    public function show(string $id)
    {
        $conversation = ChatConversation::with('employee')->findOrFail($id);

        $messages = $conversation->messages()
            ->orderBy('sentAt', 'asc')
            ->get();

        return view('admin.chat.show', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, string $id)
    {
        $data = $request->validate([
            'content' => 'required|string|min:1|max:2000',
        ]);

        $conversation = ChatConversation::findOrFail($id);
        $this->chatService->createMessage($conversation, $request->user(), trim($data['content']));

        return redirect()->route('admin.chat.show', ['id' => $id]);
    }

    public function destroy(string $id)
    {
        $conversation = ChatConversation::findOrFail($id);
        $this->chatService->deleteConversation($conversation);

        return redirect()
            ->route('admin.chat.index')
            ->with('success', 'Conversation supprimee avec succes.');
    }
}
