<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = ['owner_id', 'name', 'type', 'size', 'shared', 'path'];

    protected $casts = ['shared' => 'boolean'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function toFrontendArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
            'lastModified' => $this->updated_at?->toIso8601String(),
            'shared' => (bool) $this->shared,
            'url' => route('documents.download', $this->id),
            'ownerId' => (string) $this->owner_id,
        ];
    }
}
