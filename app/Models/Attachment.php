<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Attachment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'attachments';

    protected $fillable = [
        'demandeId',            // Reference to Demande
        'fileType',             // MIME type
        'publicURL',            // URL if stored in cloud
        'uploadedAt',           // DateTime
        'uploadedBy',           // UserId
    ];

    protected $casts = [
        'uploadedAt' => 'datetime',
    ];

    // Relationships
    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demandeId');
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploadedBy');
    }
}
