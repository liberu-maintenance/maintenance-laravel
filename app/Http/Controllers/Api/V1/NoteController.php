<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $notes = Note::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest('note_id')
            ->paginate(20);

        return response()->json($notes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content'        => 'required|string',
            'contact_id'     => 'nullable|integer|exists:contacts,contact_id',
            'company_id'     => 'nullable|integer|exists:companies,company_id',
            'opportunity_id' => 'nullable|integer',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $note = Note::create($validated);

        return response()->json($note, 201);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $note->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($note);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $note->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'content'        => 'sometimes|required|string',
            'contact_id'     => 'nullable|integer|exists:contacts,contact_id',
            'company_id'     => 'nullable|integer|exists:companies,company_id',
            'opportunity_id' => 'nullable|integer',
        ]);

        $note->update($validated);

        return response()->json($note);
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $note->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $note->delete();

        return response()->json(null, 204);
    }
}
