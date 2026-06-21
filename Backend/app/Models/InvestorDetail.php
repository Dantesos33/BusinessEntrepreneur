<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDetail extends Model
{
    protected $table = 'investor_details';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'investment_interests', 'investment_stage',
        'portfolio_companies', 'total_investments',
        'minimum_investment', 'maximum_investment',
    ];

    protected $casts = [
        'investment_interests' => 'array',
        'investment_stage' => 'array',
        'portfolio_companies' => 'array',
        'total_investments' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
