<?php

namespace App\Support;

/**
 * Ensures H2H match rows expose FT/HT scores for API clients.
 *
 * TheSports `home_scores` / `away_scores`: [full_time, halftime, ...].
 * Prefer `match_information` when present; otherwise read from score arrays.
 */
class H2hMatchScorePresenter
{
    public static function enrich(object $match): object
    {
        $homeScores = self::scoresArray($match->home_scores ?? null);
        $awayScores = self::scoresArray($match->away_scores ?? null);

        $match->home_team_score = self::resolveScore(
            $match->home_team_score ?? null,
            self::scoreAt($homeScores, 0),
        );
        $match->home_team_halftime_score = self::resolveScore(
            $match->home_team_halftime_score ?? null,
            self::scoreAt($homeScores, 1),
        );
        $match->away_team_score = self::resolveScore(
            $match->away_team_score ?? null,
            self::scoreAt($awayScores, 0),
        );
        $match->away_team_halftime_score = self::resolveScore(
            $match->away_team_halftime_score ?? null,
            self::scoreAt($awayScores, 1),
        );

        return $match;
    }

    /**
     * @param  iterable<object>  $matches
     * @return iterable<object>
     */
    public static function enrichMany(iterable $matches): iterable
    {
        foreach ($matches as $match) {
            yield self::enrich($match);
        }
    }

    private static function scoresArray(mixed $raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private static function scoreAt(?array $scores, int $index): ?int
    {
        if ($scores === null || ! array_key_exists($index, $scores)) {
            return null;
        }

        return self::toIntOrNull($scores[$index]);
    }

    private static function resolveScore(mixed $primary, ?int $fallback): ?int
    {
        $primaryInt = self::toIntOrNull($primary);

        if ($primaryInt !== null) {
            return $primaryInt;
        }

        return $fallback;
    }

    private static function toIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) && is_finite($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
