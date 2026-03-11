<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MensualiteService;
use Illuminate\Http\Request;

class MensualiteController extends Controller
{
    public function __construct(private MensualiteService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        return response()->json($this->service->paginate($perPage));
    }

    // GET /api/my/mensualites
    public function myIndex(Request $request)
    {
        $perPage = (int) $request->query('per_page', 200);
        $user = $request->user();

        return response()->json(
            $this->service->paginateByEmployee((string) $user->id, $perPage)
        );
    }

    // GET /api/my/mensualites/next-due
    public function myNextDue(Request $request)
    {
        $user = $request->user();
        $next = $this->service->nextDueByEmployee((string) $user->id);

        return response()->json([
            'next_due' => $next,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pretId' => 'required|string',
            'amount' => 'required|numeric',
            'dueDate' => 'required|date', // ✅ obligatoire sinon "next due" ne marche pas
        ]);

        $data['employeeId'] = (string) $request->user()->id;

        // ✅ FR standard
        $data['status'] = 'En attente';
        $data['submittedAt'] = now();

        return response()->json($this->service->create($data), 201);
    }

    public function show(string $id)
    {
        return response()->json($this->service->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric',
            'paidAmount' => 'nullable|numeric',
            'paidDate' => 'nullable|date',
            'status' => 'nullable|in:En attente,Approuvé,Rejeté,Payé,Actif', // ✅ si tu modifies
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
