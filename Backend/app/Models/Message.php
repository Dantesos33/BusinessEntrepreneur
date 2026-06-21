<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = ['sender_id', 'receiver_id', 'content', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public static function between(int $userIdA, int $userIdB)
    {
        return static::query()
            ->where(function ($q) use ($userIdA, $userIdB) {
                $q->where('sender_id', $userIdA)->where('receiver_id', $userIdB);
            })
            ->orWhere(function ($q) use ($userIdA, $userIdB) {
                $q->where('sender_id', $userIdB)->where('receiver_id', $userIdA);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function toFrontendArray(): array
    {
        return [
            'id' => (string) $this->id,
            'senderId' => (string) $this->sender_id,
            'receiverId' => (string) $this->receiver_id,
            'content' => $this->content,
            'timestamp' => $this->created_at?->toIso8601String(),
            'isRead' => (bool) $this->is_read,
        ];
    }
}
