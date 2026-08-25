<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchTeamStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
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
        'interceptions',
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
        'aerial_won',
        'aerial_lost',
        'duel_won',
        'duel_lost',
        'big_chance_created',
        'big_chance_missed',
        'ground_won',
        'ground_lost',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
