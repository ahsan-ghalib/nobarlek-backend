<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OddData extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'match_id',
        'type',
        'change_time',
        'match_time',
        'home_draw_away',
        'match_status',
        'goal_score',
    ];

    public function homeDrawAway(): Attribute
    {
        return Attribute::make(
            get: fn($value) => explode(',', $value)
        );
    }

    public function footballMatch(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }
}
