<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerHonor extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'honor_id',
        'season',
        'competition_id',
        'season_id',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
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
        return $this->belongsTo(Honor::class, 'honor_id', 'honor_id');
    }
}
