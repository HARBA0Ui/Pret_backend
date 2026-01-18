<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PretService;
use Illuminate\Http\Request;

class PretController extends Controller
{
    public function __construct(private PretService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        return response()->json($this->service->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employeeId' => 'required|string',
            'amount' => 'required|numeric',
            'interestRate' => 'nullable|numeric',
            'demandeId' => 'nullable|string',
        ]);

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
            'interestRate' => 'nullable|numeric',
            'remainingBalance' => 'nullable|numeric',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
