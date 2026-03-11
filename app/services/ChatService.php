<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;

class ChatService
{
    public function ensureConversationForEmployee(string $employeeId): ChatConversation
    {
        $conversation = ChatConversation::query()
            ->where('employeeId', $employeeId)
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return ChatConversation::create([
            'employeeId' => $employeeId,
            'createdAt' => now(),
            'updatedAt' => now(),
            'lastMessageAt' => null,
            'lastMessageText' => null,
            'lastMessageSenderRole' => null,
        ]);
    }

    public function createMessage(ChatConversation $conversation, User $sender, string $content): ChatMessage
    {
        $senderRole = $sender->role === 'admin' ? 'admin' : 'employee';

        $message = ChatMessage::create([
            'conversationId' => (string) $conversation->id,
            'senderId' => (string) $sender->id,
            'senderRole' => $senderRole,
            'content' => $content,
            'sentAt' => now(),
        ]);

        $updates = [
            'updatedAt' => now(),
            'lastMessageAt' => $message->sentAt,
            'lastMessageText' => $message->content,
            'lastMessageSenderRole' => $senderRole,
        ];

        if ($senderRole === 'admin' && empty($conversation->assignedAdminId)) {
            $updates['assignedAdminId'] = (string) $sender->id;
        }

        $conversation->fill($updates);
        $conversation->save();

        return $message;
    }

    public function deleteConversation(ChatConversation $conversation): void
    {
        $conversation->messages()->delete();
        $conversation->delete();
    }

    public function mapConversation(ChatConversation $conversation, bool $includeEmployee = false): array
    {
        $payload = [
            'id' => (string) $conversation->id,
            'employeeId' => (string) $conversation->employeeId,
            'assignedAdminId' => $conversation->assignedAdminId ? (string) $conversation->assignedAdminId : null,
            'createdAt' => $conversation->createdAt?->toIso8601String(),
            'updatedAt' => $conversation->updatedAt?->toIso8601String(),
            'lastMessageAt' => $conversation->lastMessageAt?->toIso8601String(),
            'lastMessageText' => $conversation->lastMessageText,
            'lastMessageSenderRole' => $conversation->lastMessageSenderRole,
        ];

        if ($includeEmployee) {
            $employee = $conversation->employee;
            $payload['employee'] = $employee ? [
                'id' => (string) $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'matricule' => $employee->matricule ?? null,
                'direction' => $employee->direction ?? null,
            ] : null;
        }

        return $payload;
    }

    public function mapMessage(ChatMessage $message): array
    {
        return [
            'id' => (string) $message->id,
            'conversationId' => (string) $message->conversationId,
            'senderId' => (string) $message->senderId,
            'senderRole' => $message->senderRole,
            'content' => $message->content,
            'sentAt' => $message->sentAt?->toIso8601String(),
        ];
    }
}
