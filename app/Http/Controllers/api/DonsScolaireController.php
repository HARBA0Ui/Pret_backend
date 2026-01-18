<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DonsScolaireService;
use Illuminate\Http\Request;

class DonsScolaireController extends Controller
{
    public function __construct(private DonsScolaireService $service) {}

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
            'studentName' => 'required|string',
            'schoolName' => 'required|string',
            'schoolLevel' => 'required|string',
            'academicYear' => 'required|string',
            'tuitionAmount' => 'nullable|numeric',
            'purpose' => 'nullable|string',
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
            'studentName' => 'nullable|string',
            'schoolName' => 'nullable|string',
            'schoolLevel' => 'nullable|string',
            'academicYear' => 'nullable|string',
            'tuitionAmount' => 'nullable|numeric',
            'purpose' => 'nullable|string',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
