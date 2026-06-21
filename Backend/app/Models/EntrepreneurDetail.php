<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntrepreneurDetail extends Model
{
    protected $table = 'entrepreneur_details';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'startup_name', 'pitch_summary', 'funding_needed',
        'industry', 'location', 'founded_year', 'team_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
