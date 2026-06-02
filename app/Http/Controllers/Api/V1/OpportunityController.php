<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $opportunities = Opportunity::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($opportunities);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'deal_size'    => 'nullable|numeric|min:0',
            'stage'        => 'required|string|max:100',
            'closing_date' => 'nullable|date',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $opportunity = Opportunity::create($validated);

        return response()->json($opportunity, 201);
    }

    public function show(Request $request, Opportunity $opportunity): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $opportunity->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($opportunity);
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $opportunity->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'deal_size'    => 'nullable|numeric|min:0',
            'stage'        => 'sometimes|required|string|max:100',
            'closing_date' => 'nullable|date',
        ]);

        $opportunity->update($validated);

        return response()->json($opportunity);
    }

    public function destroy(Request $request, Opportunity $opportunity): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $opportunity->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $opportunity->delete();

        return response()->json(null, 204);
    }
}
