<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    // ✅ View file in browser
    public function view(string $id)
    {
        $attachment = Attachment::findOrFail($id);

        if (!$attachment->filepath) {
            abort(404, 'File not found');
        }

        $filepath = storage_path('app/public/' . $attachment->filepath);

        if (!file_exists($filepath)) {
            abort(404, 'Physical file not found');
        }

        // Return file inline (opens in browser)
        return response()->file($filepath);
    }

    // ✅ Download file
    public function download(string $id)
    {
        $attachment = Attachment::findOrFail($id);

        if (!$attachment->filepath) {
            abort(404, 'File not found');
        }

        $filepath = storage_path('app/public/' . $attachment->filepath);

        if (!file_exists($filepath)) {
            abort(404, 'Physical file not found');
        }

        // Force download with original filename
        return response()->download($filepath, $attachment->filename);
    }
}
