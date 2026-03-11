<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatConversation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chat_conversations';

    protected $fillable = [
        'employeeId',
        'assignedAdminId',
        'createdAt',
        'updatedAt',
        'lastMessageAt',
        'lastMessageText',
        'lastMessageSenderRole',
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'lastMessageAt' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assignedAdminId');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversationId');
    }
}
