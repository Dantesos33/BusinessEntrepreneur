<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
        'bio',
        'is_online',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_online' => 'boolean',
        ];
    }

    public function entrepreneurDetails(): HasOne
    {
        return $this->hasOne(EntrepreneurDetail::class);
    }

    public function investorDetails(): HasOne
    {
        return $this->hasOne(InvestorDetail::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function collaborationRequestsSent(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class, 'investor_id');
    }

    public function collaborationRequestsReceived(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class, 'entrepreneur_id');
    }

    public function isEntrepreneur(): bool
    {
        return $this->role === 'entrepreneur';
    }

    public function isInvestor(): bool
    {
        return $this->role === 'investor';
    }

    public function toFrontendArray(): array
    {
        $base = [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatarUrl' => $this->avatar_url,
            'bio' => $this->bio ?? '',
            'isOnline' => (bool) $this->is_online,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];

        if ($this->isEntrepreneur() && $this->entrepreneurDetails) {
            $d = $this->entrepreneurDetails;
            return array_merge($base, [
                'startupName' => $d->startup_name,
                'pitchSummary' => $d->pitch_summary,
                'fundingNeeded' => $d->funding_needed,
                'industry' => $d->industry,
                'location' => $d->location,
                'foundedYear' => $d->founded_year,
                'teamSize' => $d->team_size,
            ]);
        }

        if ($this->isInvestor() && $this->investorDetails) {
            $d = $this->investorDetails;
            return array_merge($base, [
                'investmentInterests' => $d->investment_interests ?? [],
                'investmentStage' => $d->investment_stage ?? [],
                'portfolioCompanies' => $d->portfolio_companies ?? [],
                'totalInvestments' => $d->total_investments,
                'minimumInvestment' => $d->minimum_investment,
                'maximumInvestment' => $d->maximum_investment,
            ]);
        }

        return $base;
    }
}