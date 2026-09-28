<?php

namespace App\Support;

use App\Models\FootballMatch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

final class MatchTimeQuery
{
    /**
     * SQL expression for kickoff unix seconds.
     *
     * @param  bool  $matchTimeOnly  When true, ignore `kickoff_time` (historical / date picker).
     *                               The bare column lets MySQL use the match_time indexes.
     */
    public static function unixExpression(bool $matchTimeOnly = false): string
    {
        if ($matchTimeOnly) {
            return 'match_time';
        }

        return 'COALESCE(NULLIF(kickoff_time, 0), NULLIF(match_time, 0))';
    }

    /**
     * @param  Builder|Relation  $query
     * @return Builder|Relation
     */
    public static function applyBetween(Builder|Relation $query, int $from, int $to, bool $matchTimeOnly = false): Builder|Relation
    {
        $expr = self::unixExpression($matchTimeOnly);

        return $query->whereRaw("{$expr} BETWEEN ? AND ?", [$from, $to]);
    }

    public static function effectiveUnix(FootballMatch $match, bool $matchTimeOnly = false): int
    {
        $matchTime = (int) ($match->getRawOriginal('match_time') ?? $match->match_time ?? 0);
        if ($matchTimeOnly || ! $match->kickoff_time) {
            return $matchTime;
        }

        return (int) $match->kickoff_time ?: $matchTime;
    }

    public static function toCalendarDate(int $unix, ?string $timezone = null): ?string
    {
        if ($unix <= 0) {
            return null;
        }

        return Carbon::createFromTimestamp($unix, $timezone ?? config('app.timezone'))->format('Y-m-d');
    }
}
