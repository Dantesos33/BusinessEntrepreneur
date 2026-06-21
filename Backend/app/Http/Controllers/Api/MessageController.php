<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * List of conversation partners (for the conversation sidebar),
     * each with their most recent message in a single database pass.
     */
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Gather all the latest message rows in a single query pass
        $subquery = Message::select(DB::raw('MAX(id) as id'))
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->groupBy(DB::raw('CASE WHEN sender_id = ' . $userId . ' THEN receiver_id ELSE sender_id END'));

        $latestMessages = Message::whereIn('id', $subquery)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Extract all distinct partner IDs from the messages
        $partnerIds = $latestMessages->map(function ($msg) use ($userId) {
            return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
        })->unique()->toArray();

        // 3. 💡 EAGER LOAD: Fetch ALL user profiles and their corresponding sub-details in ONE single query join pass
        $usersMap = User::with(['entrepreneurDetails', 'investorDetails'])
            ->whereIn('id', $partnerIds)
            ->get()
            ->keyBy('id');

        // 4. Map the collection in memory instantly without hitting the database disk again
        $partners = $latestMessages->map(function ($msg) use ($userId, $usersMap) {
            $partnerId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            $partner = $usersMap->get($partnerId);

            if (!$partner)
                return null;

            return [
                'participant' => $partner->toFrontendArray(),
                'lastMessage' => $msg->toFrontendArray(),
            ];
        })->filter()->values();

        return response()->json(['conversations' => $partners]);
    }


    /**
     * Fetch the message history thread.
     */
    public function show(Request $request, $userId)
    {
        $currentUserId = $request->user()->id;

        // Uses indexed queries to stream the chat history
        $messages = Message::where(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
        })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => $m->toFrontendArray());

        // Update read status flags asynchronously
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Dispatch an outbound chat message and create a system notification row.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiverId' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiverId'],
            'content' => $validated['content'],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $validated['receiverId'],
            'type' => 'message',
            'title' => "New message from {$request->user()->name}",
            'body' => str($validated['content'])->limit(120),
            'link' => "/chat/{$request->user()->id}",
            'is_read' => false,
        ]);

        return response()->json(['message' => $message->toFrontendArray()], 201);
    }
}
