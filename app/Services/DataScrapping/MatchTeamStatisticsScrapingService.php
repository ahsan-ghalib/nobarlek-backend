<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\MatchTeamStatistic;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class MatchTeamStatisticsScrapingService
{
    use FootballScoreApiTrait;

    /** @var list<string> */
    private array $fillableColumns;

    public function __construct()
    {
        ini_set('memory_limit', -1);
        $this->fillableColumns = (new MatchTeamStatistic)->getFillable();
    }

    private function normalizeTeamStatRow(array $stats, string $matchId): array
    {
        return array_merge(
            collect($stats)->only($this->fillableColumns)->all(),
            ['match_id' => $matchId],
        );
    }

    /**
     * Scrap and inset/update the Match Team Statistics from the sports api.
     */
    public function scrapMatchTeamStatisticData(array $params = []): ?array
    {
        $matchTeamStatisticData = $this->callApi('/football/match/team_stats/list', $params);

        try {
            $results = collect($matchTeamStatisticData['results'] ?? [])->filter(fn ($row) => is_array($row));
            $matchIds = $results->pluck('id')->filter()->unique()->values()->all();
            $compByMatch = $matchIds === [] ? collect()
                : FootballMatch::query()->whereIn('match_id', $matchIds)->pluck('competition_id', 'match_id');

            $results
                ->filter(fn ($row) => ScrapingCountryScope::competitionIdAllowed($compByMatch[$row['id'] ?? ''] ?? null))
                ->chunk(300)
                ->map(function ($matchTeamStatisticResults) {
                    $matchTeamStatisticResults = collect($matchTeamStatisticResults)->map(function ($teamStatistics) {
                        return collect($teamStatistics['stats'])->map(function ($stats) use ($teamStatistics) {
                            return $this->normalizeTeamStatRow($stats, $teamStatistics['id']);
                        });
                    })->flatten(1)
                        ->chunk(300)
                        ->map(function ($chunk) {
                            MatchTeamStatistic::query()
                                ->upsert($chunk->toArray(), ['match_id', 'team_id']);
                        });
                });

            return null;
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/match/team_stats/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
