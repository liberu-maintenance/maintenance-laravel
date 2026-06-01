<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $checklists = Checklist::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($checklists);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'equipment_id' => 'nullable|integer|exists:equipment,id',
            'is_template'  => 'nullable|boolean',
            'status'       => 'nullable|string|in:active,inactive',
        ]);

        $validated['team_id']    = $request->user()->currentTeam?->id;
        $validated['created_by'] = $request->user()->id;

        $checklist = Checklist::create($validated);

        return response()->json($checklist, 201);
    }

    public function show(Request $request, Checklist $checklist): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $checklist->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($checklist);
    }

    public function update(Request $request, Checklist $checklist): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $checklist->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'equipment_id' => 'nullable|integer|exists:equipment,id',
            'is_template'  => 'nullable|boolean',
            'status'       => 'nullable|string|in:active,inactive',
        ]);

        $checklist->update($validated);

        return response()->json($checklist);
    }

    public function destroy(Request $request, Checklist $checklist): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $checklist->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $checklist->delete();

        return response()->json(null, 204);
    }
}
