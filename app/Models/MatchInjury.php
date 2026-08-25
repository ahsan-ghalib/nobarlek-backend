<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchInjury extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'player_id',
        'match_id',
        'injury_id',
        'name',
        'position',
        'logo',
        'reason',
        'start_time',
        'end_time',
        'missed_matches',
    ];
}
