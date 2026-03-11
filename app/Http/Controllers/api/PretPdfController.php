<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mensualite;
use App\Models\Pret;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PretPdfController extends Controller
{
    public function download(string $id)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        $pret = Pret::findOrFail($id);

        // 🔐 employeeId chez toi = user->id
        if ((string) $pret->employeeId !== (string) $user->id) {
            abort(403);
        }

        $mensualites = Mensualite::where('pretId', (string) $pret->id)
            ->orderBy('dueDate')
            ->get();

        $pdf = Pdf::loadView('pdf.pret-recu', [
            'pret' => $pret,
            'mensualites' => $mensualites,
            'user' => $user,
        ]);

        return $pdf->download('recu-pret-' . substr((string) $pret->id, -6) . '.pdf');
    }
}
