<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $schedules = MaintenanceSchedule::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($schedules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'equipment_id'         => 'nullable|integer|exists:equipment,id',
            'frequency_type'       => 'required|string|in:daily,weekly,monthly,yearly,hours',
            'frequency_value'      => 'required|integer|min:1',
            'next_due_date'        => 'required|date',
            'last_completed_date'  => 'nullable|date',
            'estimated_duration'   => 'nullable|integer|min:0',
            'priority'             => 'nullable|string|in:low,medium,high,critical',
            'status'               => 'nullable|string|in:active,inactive,completed',
            'assigned_to'          => 'nullable|integer|exists:users,id',
            'instructions'         => 'nullable|string',
            'checklist_id'         => 'nullable|integer|exists:checklists,id',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $schedule = MaintenanceSchedule::create($validated);

        return response()->json($schedule, 201);
    }

    public function show(Request $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $maintenanceSchedule->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($maintenanceSchedule);
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $maintenanceSchedule->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'                 => 'sometimes|required|string|max:255',
            'description'          => 'nullable|string',
            'equipment_id'         => 'nullable|integer|exists:equipment,id',
            'frequency_type'       => 'sometimes|required|string|in:daily,weekly,monthly,yearly,hours',
            'frequency_value'      => 'sometimes|required|integer|min:1',
            'next_due_date'        => 'sometimes|required|date',
            'last_completed_date'  => 'nullable|date',
            'estimated_duration'   => 'nullable|integer|min:0',
            'priority'             => 'nullable|string|in:low,medium,high,critical',
            'status'               => 'nullable|string|in:active,inactive,completed',
            'assigned_to'          => 'nullable|integer|exists:users,id',
            'instructions'         => 'nullable|string',
            'checklist_id'         => 'nullable|integer|exists:checklists,id',
        ]);

        $maintenanceSchedule->update($validated);

        return response()->json($maintenanceSchedule);
    }

    public function destroy(Request $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $maintenanceSchedule->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $maintenanceSchedule->delete();

        return response()->json(null, 204);
    }
}
