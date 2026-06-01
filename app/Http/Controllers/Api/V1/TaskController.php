<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $tasks = Task::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest('task_id')
            ->paginate(20);

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'due_date'       => 'nullable|date',
            'status'         => 'nullable|string|max:50',
            'contact_id'     => 'nullable|integer|exists:contacts,contact_id',
            'company_id'     => 'nullable|integer|exists:companies,company_id',
            'opportunity_id' => 'nullable|integer',
            'assigned_to'    => 'nullable|integer|exists:users,id',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $task->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $task->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'description'    => 'nullable|string',
            'due_date'       => 'nullable|date',
            'status'         => 'nullable|string|max:50',
            'contact_id'     => 'nullable|integer|exists:contacts,contact_id',
            'company_id'     => 'nullable|integer|exists:companies,company_id',
            'opportunity_id' => 'nullable|integer',
            'assigned_to'    => 'nullable|integer|exists:users,id',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $task->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $task->delete();

        return response()->json(null, 204);
    }
}
