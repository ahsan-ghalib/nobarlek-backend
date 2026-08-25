<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'standing_id',
        'conference',
        'group',
        'stage_id',
        'team_id',
        'promotion_id',
        'points',
        'position',
        'deduct_points',
        'note',
        'total',
        'won',
        'draw',
        'loss',
        'goals',
        'goals_against',
        'goal_diff',
        'home_points',
        'home_position',
        'home_total',
        'home_won',
        'home_draw',
        'home_loss',
        'home_goals',
        'home_goals_against',
        'home_goal_diff',
        'away_points',
        'away_position',
        'away_total',
        'away_won',
        'away_draw',
        'away_loss',
        'away_goals',
        'away_goals_against',
        'away_goal_diff',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
