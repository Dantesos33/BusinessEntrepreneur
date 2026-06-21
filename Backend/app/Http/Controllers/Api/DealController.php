<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Deal::with(['entrepreneur.entrepreneurDetails', 'investor']);

        $query = $user->isInvestor()
            ? $query->where('investor_id', $user->id)
            : $query->where('entrepreneur_id', $user->id);

        if ($search = $request->query('search')) {
            $query->whereHas('entrepreneur', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('entrepreneurDetails', fn ($q2) => $q2->where('startup_name', 'ilike', "%{$search}%"));
            });
        }

        if ($statuses = $request->query('status')) {
            $query->whereIn('status', is_array($statuses) ? $statuses : [$statuses]);
        }

        $deals = $query->get()->map(fn (Deal $d) => $d->toFrontendArray());

        return response()->json(['deals' => $deals]);
    }
}
