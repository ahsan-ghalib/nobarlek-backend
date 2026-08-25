<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayerStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'player_id',
        'team_id',
        'first',
        'goals',
        'penalty',
        'assists',
        'minutes_played',
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
        'dispossessed',
        'fouls',
        'was_fouled',
        'offsides',
        'yellow2red_cards',
        'saves',
        'punches',
        'runs_out',
        'runs_out_succ',
        'good_high_claim',
        'rating',
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
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }
}
