<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInjury extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'player_id',
        'competition_id',
        'injury_id',
        'team_id',
        'reason',
        'start_time',
        'end_time',
        'missed_matches',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
