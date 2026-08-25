<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MatchLineUp extends Model
{
    use HasFactory;

    protected $casts = [
        'first' => 'boolean',
        'captain' => 'boolean',
    ];

    public function matchPlayerStatistics(): HasOne
    {
        return $this->hasOne(MatchPlayerStatistic::class, 'match_id', 'match_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }
}
