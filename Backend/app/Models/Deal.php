<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    protected $table = 'deals';

    protected $fillable = [
        'entrepreneur_id', 'investor_id', 'amount', 'equity',
        'status', 'stage', 'last_activity_at',
    ];

    protected $casts = ['last_activity_at' => 'datetime'];

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function toFrontendArray(): array
    {
        return [
            'id' => (string) $this->id,
            'entrepreneurId' => (string) $this->entrepreneur_id,
            'investorId' => (string) $this->investor_id,
            'startupName' => $this->entrepreneur->entrepreneurDetails->startup_name ?? $this->entrepreneur->name,
            'amount' => $this->amount,
            'equity' => $this->equity,
            'status' => $this->status,
            'stage' => $this->stage,
            'lastActivity' => $this->last_activity_at?->toIso8601String() ?? $this->updated_at->toIso8601String(),
        ];
    }
}
