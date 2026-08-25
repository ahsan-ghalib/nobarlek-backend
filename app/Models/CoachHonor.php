<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachHonor extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'honor_id',
        'season',
        'competition_id',
        'season_id',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'coach_id', 'coach_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'competition_id', 'competition_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'season_id', 'season_id');
    }

    public function honor(): BelongsTo
    {
        return $this->belongsTo(CoachHonor::class, 'honor_id', 'honor_id');
    }
}
