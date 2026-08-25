<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonTeamStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'team_id',
        'matches',
        'goals',
        'penalty',
        'assists',
        'red_cards',
        'yellow_cards',
        'shots',
        'shots_on_target',
        'dribble',
        'dribble_succ',
        'clearances',
        'blocked_shots',
        'tackles',
        'passes',
        'passes_accuracy',
        'key_passes',
        'crosses',
        'crosses_accuracy',
        'long_balls',
        'long_balls_accuracy',
        'duels',
        'duels_won',
        'fouls',
        'was_fouled',
        'goals_against',
        'interceptions',
        'offsides',
        'yellow2red_cards',
        'corner_kicks',
        'ball_possession',
        'freekicks',
        'freekick_goals',
        'hit_woodwork',
        'fastbreaks',
        'fastbreak_shots',
        'fastbreak_goals',
        'poss_losts',
        'saves',
        'penalty_conceded',
        'aerial_won',
        'aerial_lost',
        'ground_won',
        'ground_lost',
        'big_chance_created',
        'big_chance_missed',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
