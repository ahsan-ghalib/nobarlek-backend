<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'competition_id',
        'year',
        'has_player_stats',
        'has_team_stats',
        'has_table',
        'is_current',
        'start_time',
        'end_time',
    ];

    /**
     * Build the display label from the season dates. The 2018 and 2019
     * editions were calendar-year seasons; newer editions use year ranges.
     */
    public function year(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (in_array((int) $value, [2018, 2019], true)) {
                    return (string) $value;
                }

                // Prefer start/end timestamps if present.
                try {
                    $start = $attributes['start_time'] ?? null;
                    $end = $attributes['end_time'] ?? null;
                    if ($start && $end) {
                        $sy = Carbon::parse($start)->year;
                        $ey = Carbon::parse($end)->year;
                        if ($sy > 0 && $ey > 0) {
                            return $sy === $ey ? (string) $ey : "{$sy}-{$ey}";
                        }
                    }
                } catch (\Throwable) {
                    // fall back below
                }

                // Without reliable dates, preserve the upstream label instead
                // of incorrectly turning a calendar year into a year range.
                return $value;
            }
        );
    }

    public function getRouteKeyName(): string
    {
        return 'season_id';
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'competition_id');
    }

    public function seasonCompetitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'cur_season_id', 'season_id');
    }

    public function seasonTeamStatistics(): HasMany
    {
        return $this->hasMany(SeasonTeamStatistics::class, 'season_id', 'season_id');
    }

    public function seasonPlayerStatistics(): HasMany
    {
        return $this->hasMany(SeasonPlayerStatistics::class, 'season_id', 'season_id');
    }

    public function teamStandings(): HasMany
    {
        return $this->hasMany(TeamStanding::class, 'season_id', 'season_id');
    }
}
