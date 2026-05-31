<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InventoryPart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryPartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $parts = InventoryPart::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($parts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_number'       => 'nullable|string|max:255',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'category'          => 'nullable|string|max:255',
            'unit_of_measure'   => 'nullable|string|max:50',
            'unit_cost'         => 'nullable|numeric|min:0',
            'reorder_level'     => 'nullable|integer|min:0',
            'reorder_quantity'  => 'nullable|integer|min:0',
            'location'          => 'nullable|string|max:255',
            'supplier'          => 'nullable|string|max:255',
            'supplier_id'       => 'nullable|integer|exists:companies,company_id',
            'lead_time_days'    => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $part = InventoryPart::create($validated);

        return response()->json($part, 201);
    }

    public function show(Request $request, InventoryPart $inventoryPart): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $inventoryPart->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($inventoryPart);
    }

    public function update(Request $request, InventoryPart $inventoryPart): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $inventoryPart->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'part_number'       => 'nullable|string|max:255',
            'name'              => 'sometimes|required|string|max:255',
            'description'       => 'nullable|string',
            'category'          => 'nullable|string|max:255',
            'unit_of_measure'   => 'nullable|string|max:50',
            'unit_cost'         => 'nullable|numeric|min:0',
            'reorder_level'     => 'nullable|integer|min:0',
            'reorder_quantity'  => 'nullable|integer|min:0',
            'location'          => 'nullable|string|max:255',
            'supplier'          => 'nullable|string|max:255',
            'supplier_id'       => 'nullable|integer|exists:companies,company_id',
            'lead_time_days'    => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
        ]);

        $inventoryPart->update($validated);

        return response()->json($inventoryPart);
    }

    public function destroy(Request $request, InventoryPart $inventoryPart): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $inventoryPart->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $inventoryPart->delete();

        return response()->json(null, 204);
    }
}
