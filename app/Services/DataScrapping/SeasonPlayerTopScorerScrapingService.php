<?php

namespace App\Services\DataScrapping;

use App\Models\SeasonTopScorer;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class SeasonPlayerTopScorerScrapingService
{
    use FootballScoreApiTrait;

    private array $params;

    /**
     * Scrap and insert/update the Newest Season Player Statistics from the sports api.
     */
    public function scrapNewestSeasonTopScorerData(array $params = []): array
    {
        $this->params = $params;
        $SeasonTopScorerData = $this->callApi('/football/season/recent/shooter/stat', $params);

        return $this->upsertSeasonTopScorers($SeasonTopScorerData);
    }

    /**
     * Scrap and insert/update the Season Player Statistics from the sports api.
     */
    public function scrapSeasonTopScorerData(array $params = []): array
    {
        $this->params = $params;
        $SeasonTopScorerData = $this->callApi('/football/season/shooter/stat', $params);

        return $this->upsertSeasonTopScorers($SeasonTopScorerData);
    }

    public function upsertSeasonTopScorers(array $SeasonTopScorerData): array
    {
        try {
            if (ScrapingCountryScope::shouldApply() && isset($this->params['uuid']) && ! ScrapingCountryScope::seasonIdAllowed((string) $this->params['uuid'])) {
                return $SeasonTopScorerData['query'] ?? [];
            }

            if (count($SeasonTopScorerData['results'] ?? []) > 0) {
                $seasonStatistics = collect($SeasonTopScorerData['results'])
                    ->transform(function ($stats) {
                        $stats = array_merge($stats, [
                            'player_id' => $stats['player']['id'],
                            'team_id' => $stats['team']['id'],
                            'season_id' => $this->params['uuid'],
                            'updated_at' => Carbon::parse($stats['updated_at']),
                        ]);
                        //                        dd($stats);
                        unset($stats['player']);
                        unset($stats['team']);

                        return $stats;
                    });

                SeasonTopScorer::query()
                    ->upsert($seasonStatistics->toArray(), ['season_id', 'player_id', 'team_id']);
            }

            return $SeasonTopScorerData['query'] ?? [];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/season/shooter/stat: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
