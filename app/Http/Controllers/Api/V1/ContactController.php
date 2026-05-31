<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $contacts = Contact::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $contact = Contact::create($validated);

        return response()->json($contact, 201);
    }

    public function show(Request $request, Contact $contact): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $contact->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($contact);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $contact->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
        ]);

        $contact->update($validated);

        return response()->json($contact);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $contact->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $contact->delete();

        return response()->json(null, 204);
    }
}
