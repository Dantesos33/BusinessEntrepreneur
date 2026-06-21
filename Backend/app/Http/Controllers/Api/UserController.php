<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function investors(Request $request)
    {
        $query = User::where('role', 'investor')->with('investorDetails');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('bio', 'ilike', "%{$search}%");
            });
        }

        $investors = $query->get()->map(fn (User $u) => $u->toFrontendArray());

        return response()->json(['investors' => $investors]);
    }

    public function entrepreneurs(Request $request)
    {
        $query = User::where('role', 'entrepreneur')->with('entrepreneurDetails');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('entrepreneurDetails', function ($q2) use ($search) {
                      $q2->where('startup_name', 'ilike', "%{$search}%")
                         ->orWhere('industry', 'ilike', "%{$search}%");
                  });
            });
        }

        $entrepreneurs = $query->get()->map(fn (User $u) => $u->toFrontendArray());

        return response()->json(['entrepreneurs' => $entrepreneurs]);
    }

    public function show(int $id)
    {
        $user = User::with(['entrepreneurDetails', 'investorDetails'])->findOrFail($id);

        return response()->json(['user' => $user->toFrontendArray()]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id !== $request->user()->id) {
            abort(403, 'You can only edit your own profile.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'sometimes|nullable|string|max:1000',

            // Entrepreneur fields
            'startupName' => 'sometimes|nullable|string|max:255',
            'pitchSummary' => 'sometimes|nullable|string',
            'fundingNeeded' => 'sometimes|nullable|string|max:50',
            'industry' => 'sometimes|nullable|string|max:100',
            'location' => 'sometimes|nullable|string|max:100',
            'foundedYear' => 'sometimes|nullable|integer',
            'teamSize' => 'sometimes|nullable|integer',

            // Investor fields
            'investmentInterests' => 'sometimes|array',
            'investmentStage' => 'sometimes|array',
            'minimumInvestment' => 'sometimes|nullable|string|max:50',
            'maximumInvestment' => 'sometimes|nullable|string|max:50',
        ]);

        $user->update(array_filter([
            'name' => $validated['name'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ], fn ($v) => $v !== null));

        if ($user->isEntrepreneur()) {
            $user->entrepreneurDetails()->updateOrCreate(['user_id' => $user->id], array_filter([
                'startup_name' => $validated['startupName'] ?? null,
                'pitch_summary' => $validated['pitchSummary'] ?? null,
                'funding_needed' => $validated['fundingNeeded'] ?? null,
                'industry' => $validated['industry'] ?? null,
                'location' => $validated['location'] ?? null,
                'founded_year' => $validated['foundedYear'] ?? null,
                'team_size' => $validated['teamSize'] ?? null,
            ], fn ($v) => $v !== null));
        }

        if ($user->isInvestor()) {
            $user->investorDetails()->updateOrCreate(['user_id' => $user->id], array_filter([
                'investment_interests' => $validated['investmentInterests'] ?? null,
                'investment_stage' => $validated['investmentStage'] ?? null,
                'minimum_investment' => $validated['minimumInvestment'] ?? null,
                'maximum_investment' => $validated['maximumInvestment'] ?? null,
            ], fn ($v) => $v !== null));
        }

        return response()->json([
            'user' => $user->fresh(['entrepreneurDetails', 'investorDetails'])->toFrontendArray(),
        ]);
    }
}
