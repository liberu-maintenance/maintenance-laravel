<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $equipment = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($equipment);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'serial_number'    => 'nullable|string|max:255',
            'model'            => 'nullable|string|max:255',
            'manufacturer'     => 'nullable|string|max:255',
            'category'         => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'purchase_date'    => 'nullable|date',
            'warranty_expiry'  => 'nullable|date',
            'status'           => 'nullable|string|in:active,inactive,under_maintenance,retired',
            'criticality'      => 'nullable|string|in:low,medium,high,critical',
            'notes'            => 'nullable|string',
            'company_id'       => 'nullable|integer|exists:companies,company_id',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $equipment = Equipment::create($validated);

        return response()->json($equipment, 201);
    }

    public function show(Request $request, Equipment $equipment): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $equipment->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($equipment);
    }

    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $equipment->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'serial_number'    => 'nullable|string|max:255',
            'model'            => 'nullable|string|max:255',
            'manufacturer'     => 'nullable|string|max:255',
            'category'         => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'purchase_date'    => 'nullable|date',
            'warranty_expiry'  => 'nullable|date',
            'status'           => 'nullable|string|in:active,inactive,under_maintenance,retired',
            'criticality'      => 'nullable|string|in:low,medium,high,critical',
            'notes'            => 'nullable|string',
            'company_id'       => 'nullable|integer|exists:companies,company_id',
        ]);

        $equipment->update($validated);

        return response()->json($equipment);
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $equipment->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $equipment->delete();

        return response()->json(null, 204);
    }
}
