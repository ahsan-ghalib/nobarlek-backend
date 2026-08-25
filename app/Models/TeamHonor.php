<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamHonor extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'honor_id',
        'season',
        'competition_id',
        'season_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'competition_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function honor(): BelongsTo
    {
        return $this->belongsTo(Honor::class, 'honor_id', 'honor_id');
    }
}
