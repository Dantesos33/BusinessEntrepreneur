<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollaborationRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class CollaborationRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->isInvestor()
            ? CollaborationRequest::with('entrepreneur')->where('investor_id', $user->id)
            : CollaborationRequest::with('investor')->where('entrepreneur_id', $user->id);

        $requests = $query->latest()->get()->map(fn ($r) => $r->toFrontendArray());

        return response()->json(['collaborationRequests' => $requests]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->isInvestor()) {
            abort(403, 'Only investors can send collaboration requests.');
        }

        $validated = $request->validate([
            'entrepreneurId' => 'required|exists:users,id',
            'message' => 'required|string|min:10',
        ]);

        $collaborationRequest = CollaborationRequest::create([
            'investor_id' => $user->id,
            'entrepreneur_id' => $validated['entrepreneurId'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        Notification::create([
            'user_id' => $validated['entrepreneurId'],
            'type' => 'collaboration_request',
            'title' => "{$user->name} sent you a collaboration request",
            'body' => $validated['message'],
            'link' => '/dashboard/entrepreneur',
            'is_read' => false,
        ]);

        return response()->json(['collaborationRequest' => $collaborationRequest->toFrontendArray()], 201);
    }

    public function update(Request $request, int $id)
    {
        $collaborationRequest = CollaborationRequest::findOrFail($id);

        if ($collaborationRequest->entrepreneur_id !== $request->user()->id) {
            abort(403, 'Only the recipient entrepreneur can update this request.');
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $collaborationRequest->update(['status' => $validated['status']]);

        if ($validated['status'] === 'accepted') {
            Notification::create([
                'user_id' => $collaborationRequest->investor_id,
                'type' => 'collaboration_accepted',
                'title' => "{$request->user()->name} accepted your collaboration request",
                'body' => null,
                'link' => "/profile/entrepreneur/{$collaborationRequest->entrepreneur_id}",
                'is_read' => false,
            ]);
        }

        return response()->json(['collaborationRequest' => $collaborationRequest->toFrontendArray()]);
    }
}
