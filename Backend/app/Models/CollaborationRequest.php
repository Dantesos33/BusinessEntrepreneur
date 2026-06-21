<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationRequest extends Model
{
    protected $table = 'collaboration_requests';

    protected $fillable = ['investor_id', 'entrepreneur_id', 'message', 'status'];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }

    public function toFrontendArray(): array
    {
        return [
            'id' => (string) $this->id,
            'investorId' => (string) $this->investor_id,
            'entrepreneurId' => (string) $this->entrepreneur_id,
            'message' => $this->message,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toIso8601String(),
            // Embedded so the frontend never needs a second lookup —
            // only populated when the relation was eager-loaded.
            'investor' => $this->relationLoaded('investor') ? $this->investor->toFrontendArray() : null,
            'entrepreneur' => $this->relationLoaded('entrepreneur') ? $this->entrepreneur->toFrontendArray() : null,
        ];
    }
}
