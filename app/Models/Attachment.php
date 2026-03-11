<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Attachment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'attachments';

    protected $fillable = [
        'demandeId',
        'filename',          // ✅ ADDED - Original filename
        'filepath',          // ✅ ADDED - Storage path
        'mimetype',          // ✅ ADDED - File MIME type
        'filesize',          // ✅ ADDED - File size in bytes
        'type',              // ✅ ADDED - Type: demande, pret, other
        'publicURL',         // URL if stored in cloud
        'uploadedAt',
        'uploadedBy',
    ];

    protected $casts = [
        'uploadedAt' => 'datetime',
        'filesize' => 'integer',
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
