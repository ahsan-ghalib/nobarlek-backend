<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\MatchPlayerStatistic;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class MatchPlayerStatisticsScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Match Player Statistics from the sports api.
     */
    public function scrapMatchPlayerStatisticData(array $params = []): ?array
    {
        $matchPlayerStatisticData = $this->callApi('/football/match/player_stats/list', $params);

        try {
            $raw = collect($matchPlayerStatisticData['results'] ?? [])->filter(fn ($row) => is_array($row));
            $matchIds = $raw->pluck('id')->filter()->unique()->values()->all();
            $compByMatch = $matchIds === [] ? collect()
                : FootballMatch::query()->whereIn('match_id', $matchIds)->pluck('competition_id', 'match_id');

            $raw
                ->filter(fn ($row) => ScrapingCountryScope::competitionIdAllowed($compByMatch[$row['id'] ?? ''] ?? null))
                ->chunk(100)
                ->map(function ($matchPlayerStatisticResults) {
                    $playerMatchStatistics = collect($matchPlayerStatisticResults)->map(function ($match) {
                        return collect($match['player_stats'])->map(function ($playerStats) use ($match) {
                            return array_merge($playerStats, ['match_id' => $match['id']]);
                        });
                    })
                        ->flatten(1)->chunk(300)->map(function ($chunk) {
                            MatchPlayerStatistic::query()
                                ->upsert($chunk->toArray(), ['match_id', 'player_id', 'team_id']);
                        });
                });

            return null;
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/match/player_stats/list:'.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
