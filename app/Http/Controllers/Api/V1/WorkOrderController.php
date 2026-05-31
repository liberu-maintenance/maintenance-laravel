<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $workOrders = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($workOrders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'priority'     => 'nullable|string|in:low,medium,high,urgent',
            'status'       => 'nullable|string|in:pending,approved,rejected,in_progress,completed',
            'location'     => 'nullable|string|max:255',
            'equipment_id' => 'nullable|integer|exists:equipment,id',
            'due_date'     => 'nullable|date',
            'assigned_to'  => 'nullable|integer|exists:users,id',
            'notes'        => 'nullable|string',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;
        $validated['submitted_at'] = now();

        $workOrder = WorkOrder::create($validated);

        return response()->json($workOrder, 201);
    }

    public function show(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $workOrder->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($workOrder);
    }

    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $workOrder->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'description'  => 'nullable|string',
            'priority'     => 'nullable|string|in:low,medium,high,urgent',
            'status'       => 'nullable|string|in:pending,approved,rejected,in_progress,completed',
            'location'     => 'nullable|string|max:255',
            'equipment_id' => 'nullable|integer|exists:equipment,id',
            'due_date'     => 'nullable|date',
            'assigned_to'  => 'nullable|integer|exists:users,id',
            'notes'        => 'nullable|string',
        ]);

        $workOrder->update($validated);

        return response()->json($workOrder);
    }

    public function destroy(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $workOrder->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $workOrder->delete();

        return response()->json(null, 204);
    }
}
