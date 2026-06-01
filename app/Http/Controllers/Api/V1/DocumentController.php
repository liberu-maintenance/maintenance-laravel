<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $documents = Document::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest()
            ->paginate(20);

        return response()->json($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'document_type'      => 'nullable|string|max:100',
            'file_path'          => 'nullable|string|max:500',
            'file_name'          => 'nullable|string|max:255',
            'mime_type'          => 'nullable|string|max:100',
            'file_size'          => 'nullable|integer|min:0',
            'version'            => 'nullable|string|max:50',
            'status'             => 'nullable|string|in:draft,active,archived',
            'compliance_standard'=> 'nullable|string|max:100',
            'effective_date'     => 'nullable|date',
            'expiry_date'        => 'nullable|date',
            'review_date'        => 'nullable|date',
            'approval_status'    => 'nullable|string|in:pending,approved,rejected',
        ]);

        $validated['team_id']    = $request->user()->currentTeam?->id;
        $validated['created_by'] = $request->user()->id;

        $document = Document::create($validated);

        return response()->json($document, 201);
    }

    public function show(Request $request, Document $document): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $document->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($document);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $document->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'description'        => 'nullable|string',
            'document_type'      => 'nullable|string|max:100',
            'file_path'          => 'nullable|string|max:500',
            'file_name'          => 'nullable|string|max:255',
            'mime_type'          => 'nullable|string|max:100',
            'file_size'          => 'nullable|integer|min:0',
            'version'            => 'nullable|string|max:50',
            'status'             => 'nullable|string|in:draft,active,archived',
            'compliance_standard'=> 'nullable|string|max:100',
            'effective_date'     => 'nullable|date',
            'expiry_date'        => 'nullable|date',
            'review_date'        => 'nullable|date',
            'approval_status'    => 'nullable|string|in:pending,approved,rejected',
        ]);

        $validated['updated_by'] = $request->user()->id;

        $document->update($validated);

        return response()->json($document);
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $document->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}
