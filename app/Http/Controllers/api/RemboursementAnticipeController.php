<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RemboursementAnticipeService;
use Illuminate\Http\Request;

class RemboursementAnticipeController extends Controller
{
    public function __construct(private RemboursementAnticipeService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        return response()->json($this->service->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employeeId' => 'required|string',
            'amountRequested' => 'required|numeric',
            'loanIdToRepay' => 'nullable|string',
            'earlyRepaymentAmount' => 'nullable|numeric',
            'penaltyWaived' => 'nullable|boolean',
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
            'amountRequested' => 'nullable|numeric',
            'loanIdToRepay' => 'nullable|string',
            'earlyRepaymentAmount' => 'nullable|numeric',
            'penaltyWaived' => 'nullable|boolean',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
