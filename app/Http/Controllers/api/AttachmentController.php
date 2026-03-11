<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'demandeId' => 'nullable|string',
            'type' => 'nullable|in:demande,pret,other',
            'category' => 'nullable|in:cnss,other', // ✅ NEW (optional)
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'demandeId' => $request->demandeId,
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
            'mimetype' => $file->getMimeType(),
            'filesize' => $file->getSize(),
            'type' => $request->type ?? 'demande',
            'category' => $request->category ?? 'other', // ✅ NEW (remove if no column)
            'uploadedBy' => (string) $request->user()->id,
            'uploadedAt' => now(),
        ]);

        return response()->json([
            'success' => true,
            'attachment' => $attachment,
            'url' => Storage::url($path),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $attachment = Attachment::findOrFail($id);

        if ($attachment->uploadedBy !== (string) $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attachment->filepath) {
            Storage::disk('public')->delete($attachment->filepath);
        }

        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function download(string $id)
    {
        $attachment = Attachment::findOrFail($id);

        if (!$attachment->filepath) {
            return response()->json(['error' => 'File not found'], 404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download($attachment->filepath, $attachment->filename);
    }
}
