<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'competition_id',
        'match_status',
        'match_time',
        'kick_off',
        'home_team_id',
        'home_league_ranking',
        'home_team_score',
        'home_team_halftime_score',
        'home_team_red_cards',
        'home_team_yellow_cards',
        'home_team_corners',
        'home_team_overtime_score',
        'home_team_penalty_shootout_score',
        'away_team_id',
        'away_league_ranking',
        'away_team_score',
        'away_team_halftime_score',
        'away_team_red_cards',
        'away_team_yellow_cards',
        'away_team_corners',
        'away_team_overtime_score',
        'away_team_penalty_shootout_score',
        'asian_plate_home',
        'european_plate',
        'size_ball_plate',
        'corner_plate',
        'match_description',
        'is_it_neutral',
        'match_round',
        'season_id',
        'season_year',
    ];
}
