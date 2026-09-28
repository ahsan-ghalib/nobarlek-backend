<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\MatchPlayerStatistic;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Illuminate\Support\Facades\Schema;

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
                                ->upsert($this->toTableRows($chunk->all()), ['match_id', 'player_id', 'team_id']);
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

    /**
     * TheSports adds stat fields over time (e.g. big_chance_created); unknown keys made
     * the whole insert fail. Keep only table columns, and give every row the same keys
     * because a multi-row upsert takes its column list from the first row.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function toTableRows(array $rows): array
    {
        static $columns = null;
        $columns ??= array_flip(array_diff(
            Schema::getColumnListing((new MatchPlayerStatistic)->getTable()),
            ['id', 'created_at', 'updated_at'],
        ));

        $rows = array_map(fn (array $row) => array_intersect_key($row, $columns), $rows);
        $keys = array_fill_keys(array_keys(array_merge(...$rows)), null);

        return array_map(fn (array $row) => array_merge($keys, $row), $rows);
    }
}
