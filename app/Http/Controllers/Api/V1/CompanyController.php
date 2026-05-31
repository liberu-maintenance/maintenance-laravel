<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        $companies = Company::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->latest('company_id')
            ->paginate(20);

        return response()->json($companies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'zip'            => 'nullable|string|max:20',
            'phone_number'   => 'nullable|string|max:50',
            'website'        => 'nullable|url|max:255',
            'industry'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'type'           => 'nullable|string|in:customer,supplier,vendor,both',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'payment_terms'  => 'nullable|string|max:100',
            'is_active'      => 'nullable|boolean',
        ]);

        $validated['team_id'] = $request->user()->currentTeam?->id;

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    public function show(Request $request, Company $company): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $company->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($company);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $company->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'zip'            => 'nullable|string|max:20',
            'phone_number'   => 'nullable|string|max:50',
            'website'        => 'nullable|url|max:255',
            'industry'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'type'           => 'nullable|string|in:customer,supplier,vendor,both',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'payment_terms'  => 'nullable|string|max:100',
            'is_active'      => 'nullable|boolean',
        ]);

        $company->update($validated);

        return response()->json($company);
    }

    public function destroy(Request $request, Company $company): JsonResponse
    {
        $teamId = $request->user()->currentTeam?->id;

        if ($teamId && $company->team_id !== $teamId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $company->delete();

        return response()->json(null, 204);
    }
}
