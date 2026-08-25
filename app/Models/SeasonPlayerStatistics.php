<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class SeasonPlayerStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'player_id',
        'team_id',
        'matches',
        'court',
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
        'ground_won',
        'ground_lost',
        'big_chance_created',
        'big_chance_missed',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function competition(): HasOneThrough
    {
        return $this->hasOneThrough(Competition::class, Season::class, 'season_id', 'competition_id', 'season_id', 'competition_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }
}
