<?php

namespace App\Models;

use App\Enums\MatchStateEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class FootballMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'season_id',
        'competition_id',
        'home_team_id',
        'away_team_id',
        'status_id',
        'match_time',
        'kickoff_time',
        'venue_id',
        'referee_id',
        'neutral',
        'note',
        'home_scores',
        'away_scores',
        'home_position',
        'away_position',
        'mlive',
        'lineup',
        'stage_id',
        'group_num',
        'round_num',
        'related_id',
        'agg_score',
        'environment',
        'away_formation',
        'home_formation',
        'away_coach_id',
        'home_coach_id',
        'confirmed',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_level',
        'match_information_html',
    ];

    protected $casts = [
        'home_scores' => 'array',
        'away_scores' => 'array',
        'environment' => 'array',
        /** Unix seconds — must stay numeric in JSON for API clients (accessors must not replace this). */
        'match_time' => 'integer',
        'kickoff_time' => 'integer',
    ];

    protected $appends = [
        'calculated_match_time',
        'match_time_formatted',
    ];

    public function metaKeywords(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? explode(', ', $value) : [],
            set: fn($value) => is_array($value) ? implode(', ', $value) : $value
        );
    }

    /**
     * Approximate minutes from the first-half kick-off to the second-half kick-off
     * (45' plus stoppage time plus the break). Only used when the live feed has not
     * given us the second-half kick-off yet.
     */
    private const FIRST_HALF_AND_BREAK_MINUTES = 62;

    public function calculatedMatchTime(): Attribute
    {
        return new Attribute(get: function () {
            $matchStatus = $this->attributes['status_id'] ?? null;
            $scheduled = (int) ($this->attributes['match_time'] ?? 0);
            // TheSports `kickoff_time` is the kick-off of the half in progress (first
            // half, second half, extra time); 0 until the live feed has sent it.
            $kickoff = (int) ($this->attributes['kickoff_time'] ?? 0);

            return match ($matchStatus) {
                null, '' => '-',
                MatchStateEnum::NOT_STARTED->value => '-',
                MatchStateEnum::HALF_TIME->value => 'HT',
                MatchStateEnum::END->value => 'FT',
                MatchStateEnum::PENALTY_SHOOT_OUT->value => 'PEN',
                MatchStateEnum::FIRST_HALF->value => $this->runningMinute($kickoff ?: $scheduled, 1, 45),
                MatchStateEnum::SECOND_HALF->value => $this->runningMinute($this->secondHalfKickoff($kickoff, $scheduled), 46, 90),
                MatchStateEnum::OVERTIME->value,
                MatchStateEnum::OVERTIME_DEPRECATED->value => $this->overtimeMinute($kickoff, $scheduled),
                default => $matchStatus,
            };
        });
    }

    /**
     * The stored kick-off can still be the first-half one (or missing) right after
     * the second half starts. Counting from it added the break to the clock and
     * showed 90+ around the hour mark, so estimate the restart in that case.
     */
    private function secondHalfKickoff(int $kickoff, int $scheduled): int
    {
        $firstHalfStart = $scheduled ?: $kickoff;
        if ($kickoff > 0 && $firstHalfStart > 0 && $kickoff >= $firstHalfStart + 50 * 60) {
            return $kickoff;
        }

        $base = $kickoff ?: $scheduled;

        return $base > 0 ? $base + self::FIRST_HALF_AND_BREAK_MINUTES * 60 : 0;
    }

    private function overtimeMinute(int $kickoff, int $scheduled): int|string
    {
        // Extra time starts well after the scheduled kick-off; anything earlier is a stale value.
        if ($kickoff > 0 && $scheduled > 0 && $kickoff >= $scheduled + 100 * 60) {
            return $this->runningMinute($kickoff, 91, 120);
        }

        return 'ET';
    }

    private function runningMinute(int $start, int $first, int $last): int|string
    {
        if ($start <= 0) {
            return '-';
        }

        $minute = $first + intdiv(max(0, now()->timestamp - $start), 60);

        return $minute > $last ? "{$last}+" : $minute;
    }

    /**
     * Use the scheduled match timestamp when the live feed did not provide a
     * kickoff timestamp. Several API consumers render their time from this
     * field directly.
     */
    public function kickoffTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (int) $value ?: (int) ($this->attributes['match_time'] ?? 0),
        );
    }

    public function matchTimeFormatted(): Attribute
    {
        return Attribute::make(get: function () {
            $ts = $this->attributes['match_time'] ?? null;
            if (!$ts || !is_numeric($ts)) {
                return null;
            }

            $timezone = \App\Support\ViewerTimeZone::fromRequest();

            try {
                return Carbon::createFromTimestamp((int) $ts)
                    ->timezone($timezone)
                    ->format('d M Y, H:i');
            } catch (\Throwable) {
                return null;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'match_id';
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'competition_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id', 'team_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee_id', 'referee_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id', 'team_id');
    }

    public function homeCoach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'home_coach_id', 'coach_id');
    }

    public function awayCoach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'away_coach_id', 'coach_id');
    }

    public function matchInformation(): HasOne
    {
        return $this->hasOne(MatchInformation::class, 'match_id', 'match_id')
            ->withDefault(function (MatchInformation $information, FootballMatch $match) {
                $information->forceFill($match->fallbackMatchInformation());
            });
    }

    public function fallbackMatchInformation(): array
    {
        $homeScores = self::scoreArray($this->home_scores);
        $awayScores = self::scoreArray($this->away_scores);

        return [
            'match_id' => $this->match_id,
            'competition_id' => $this->competition_id,
            'match_status' => $this->status_id,
            'match_time' => $this->match_time,
            'kick_off' => $this->kickoff_time,
            'home_team_id' => $this->home_team_id,
            'home_team_score' => self::scoreValue($homeScores, 0),
            'home_team_halftime_score' => self::scoreValue($homeScores, 1),
            'home_team_overtime_score' => self::scoreValue($homeScores, 5),
            'home_team_penalty_shootout_score' => self::scoreValue($homeScores, 6),
            'away_team_id' => $this->away_team_id,
            'away_team_score' => self::scoreValue($awayScores, 0),
            'away_team_halftime_score' => self::scoreValue($awayScores, 1),
            'away_team_overtime_score' => self::scoreValue($awayScores, 5),
            'away_team_penalty_shootout_score' => self::scoreValue($awayScores, 6),
            'match_round' => $this->round_num,
            'season_id' => $this->season_id,
        ];
    }

    private static function scoreArray(mixed $scores): array
    {
        if (is_array($scores)) {
            return $scores;
        }

        if (is_string($scores)) {
            $decoded = json_decode($scores, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private static function scoreValue(array $scores, int $index): ?int
    {
        $value = $scores[$index] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    public function matchLineups(): HasMany
    {
        return $this->hasMany(MatchLineUp::class, 'match_id', 'match_id');
    }

    public function matchInjuries(): HasMany
    {
        return $this->hasMany(MatchInjury::class, 'match_id', 'match_id');
    }

    public function matchTeamStatistics(): HasMany
    {
        return $this->hasMany(MatchTeamStatistic::class, 'match_id', 'match_id');
    }

    public function homeTeamStatistic(): HasOne
    {
        return $this->hasOne(MatchTeamStatistic::class, 'team_id', 'home_team_id')->latestOfMany();
    }

    public function awayTeamStatistic(): HasOne
    {
        return $this->hasOne(MatchTeamStatistic::class, 'team_id', 'away_team_id')->latestOfMany();
    }

    public function matchIncidents(): HasMany
    {
        return $this->hasMany(MatchIncidents::class, 'match_id', 'match_id');
    }

    public function matchChartStatistic(): HasOne
    {
        return $this->hasOne(MatchChartStatistic::class, 'match_id', 'match_id');
    }

    public function matchStatistics(): HasOne
    {
        return $this->hasOne(MatchStatistic::class, 'match_id', 'match_id');
    }

    public function matchChat(): HasMany
    {
        return $this->hasMany(MatchChat::class, 'match_id', 'match_id');
    }
    public function matchOdds(): HasMany
    {
        return $this->hasMany(OddData::class, 'match_id', 'match_id');
    }

    public function matchOdd(): HasOne
    {
        return $this->hasOne(OddData::class, 'match_id', 'match_id')->ofMany([
            'change_time' => 'max',
            'id' => 'max',
        ], function ($query) {
            $query->where('company_id', '=', 2)->where('type', '=', request()->odd_type);
        });
    }

    public function matchPlayersStatistics(): HasMany
    {
        return $this->hasMany(MatchPlayerStatistic::class, 'match_id', 'match_id');
    }

    public function matchPlayerStatistics(): HasOne
    {
        return $this->hasOne(MatchPlayerStatistic::class, 'match_id', 'match_id');
    }

    public function stream(): HasOne
    {
        return $this->hasOne(MatchStream::class, 'match_id', 'match_id');
    }
}
