<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chat_messages';

    protected $fillable = [
        'conversationId',
        'senderId',
        'senderRole',
        'content',
        'sentAt',
    ];

    protected $casts = [
        'sentAt' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversationId');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'senderId');
    }
}
