<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchIncidents extends Model
{
    use HasFactory;

    protected $casts = [
        'time' => 'integer',
    ];

    protected $fillable = [
        'type',
        'position',
        'time',
        'match_id',
        'player_id',
        'player_name',
        'assist1_id',
        'assist1_name',
        'assist2_id',
        'assist2_name',
        'home_score',
        'away_score',
        'in_player_id',
        'in_player_name',
        'out_player_id',
        'out_player_name',
        'var_reason',
        'var_result',
        'reason_type',
    ];
}
