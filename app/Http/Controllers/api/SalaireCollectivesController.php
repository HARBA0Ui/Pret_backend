<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SalaireCollectivesService;
use Illuminate\Http\Request;

class SalaireCollectivesController extends Controller
{
    public function __construct(private SalaireCollectivesService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        return response()->json($this->service->paginate($perPage));
    }

    // NEW: GET /api/my/salaires-collectives
    public function myIndex(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $user = $request->user();

        return response()->json(
            $this->service->paginateByEmployee((string) $user->id, $perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amountRequested' => 'required|numeric',
            'reason' => 'nullable|string|max:255',  // ✅ ADD
            'collectiveAgreement' => 'nullable|string',
            'beneficiaryType' => 'nullable|string',
            'disbursementSchedule' => 'nullable|string',
            'description' => 'nullable|string', // ✅ ADDED
            'attachmentIds' => 'nullable|array', // ✅ ADDED
            'attachmentIds.*' => 'string', // ✅ ADDED
        ]);

        $data['employeeId'] = (string) $request->user()->id;
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
            'amountRequested' => 'nullable|numeric',
            'collectiveAgreement' => 'nullable|string',
            'beneficiaryType' => 'nullable|string',
            'disbursementSchedule' => 'nullable|string',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
