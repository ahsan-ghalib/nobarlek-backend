<?php

namespace App\Support;

use App\Models\SeasonPlayerStatistics;
use App\Models\SeasonTopScorer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Top scorer / top assist lists for a season.
 *
 * TheSports has two sources: the shooter table (official scorer ranking, not
 * available for every competition) and per-player season stats (every player,
 * including assists). Each list reads its natural source first and falls back
 * to the other so a competition with only one feed still shows both lists.
 */
class SeasonPlayerLeaders
{
    private const RELATIONS = [
        'team:team_id,name,short_name,logo',
        'player:player_id,name,short_name,logo',
    ];

    public static function topScorers(?string $seasonId, int $limit = 20): Collection
    {
        if (blank($seasonId)) {
            return collect();
        }

        $rank = fn (Builder $query) => $query
            ->where('season_id', $seasonId)
            ->where('goals', '>', 0)
            ->orderByDesc('goals')
            ->orderByDesc('assists')
            ->orderBy('penalty')
            ->limit($limit)
            ->with(self::RELATIONS);

        $rows = $rank(SeasonTopScorer::query()->select(['season_id', 'player_id', 'team_id', 'goals', 'penalty', 'assists', 'minutes_played']))->get();

        if ($rows->isEmpty()) {
            $rows = $rank(self::playerStatsQuery())->get();
        }

        return $rows->values();
    }

    public static function topAssists(?string $seasonId, int $limit = 20): Collection
    {
        if (blank($seasonId)) {
            return collect();
        }

        $rank = fn (Builder $query) => $query
            ->where('season_id', $seasonId)
            ->where('assists', '>', 0)
            ->orderByDesc('assists')
            ->orderByDesc('goals')
            ->limit($limit)
            ->with(self::RELATIONS);

        $rows = $rank(self::playerStatsQuery())->get();

        if ($rows->isEmpty()) {
            $rows = $rank(SeasonTopScorer::query()->select(['season_id', 'player_id', 'team_id', 'goals', 'penalty', 'assists', 'minutes_played']))->get();
        }

        return $rows->values();
    }

    private static function playerStatsQuery(): Builder
    {
        return SeasonPlayerStatistics::query()
            ->select(['season_id', 'player_id', 'team_id', 'matches', 'goals', 'penalty', 'assists', 'minutes_played']);
    }
}
