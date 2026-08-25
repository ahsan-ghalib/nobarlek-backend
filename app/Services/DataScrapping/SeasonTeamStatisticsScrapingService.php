<?php

namespace App\Services\DataScrapping;

use App\Models\SeasonTeamStatistics;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class SeasonTeamStatisticsScrapingService
{
    use FootballScoreApiTrait;

    private array $params;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and insert/update the Newest Season Team Statistics from the sports api.
     */
    public function scrapNewestSeasonTeamStatisticData(array $params = []): array
    {
        $this->params = $params;
        $seasonTeamStatisticData = $this->callApi('/football/season/recent/team/stat', $params);

        return $this->upsertSeasonTeamStatistics($seasonTeamStatisticData);
    }

    /**
     * Scrap and insert/update the Season Team Statistics from the sports api.
     */
    public function scrapSeasonTeamStatisticData(array $params = []): array
    {
        $this->params = $params;
        $seasonTeamStatisticData = $this->callApi('/football/season/team/stat', $params);

        return $this->upsertSeasonTeamStatistics($seasonTeamStatisticData);
    }

    public function upsertSeasonTeamStatistics(array $seasonTeamStatisticData): array
    {
        try {
            if (ScrapingCountryScope::shouldApply() && isset($this->params['uuid']) && ! ScrapingCountryScope::seasonIdAllowed((string) $this->params['uuid'])) {
                return $seasonTeamStatisticData['query'] ?? [];
            }

            if (count($seasonTeamStatisticData['results'] ?? []) > 0) {
                $allowed = array_flip(array_merge(
                    (new SeasonTeamStatistics)->getFillable(),
                    ['season_id', 'team_id', 'created_at', 'updated_at'],
                ));

                collect($seasonTeamStatisticData['results'] ?? [])
                    ->map(function ($stats) use ($allowed) {
                        $stats = array_merge($stats, [
                            'team_id' => $stats['team']['id'],
                            'season_id' => $this->params['uuid'],
                            'updated_at' => Carbon::parse($stats['updated_at']),
                        ]);
                        unset($stats['team']);

                        return array_intersect_key($stats, $allowed);
                    })->chunk(300)->each(function ($chunk) {
                        SeasonTeamStatistics::query()
                            ->upsert($chunk->toArray(), ['season_id', 'team_id']);
                    });
            }

            return $seasonTeamStatisticData['query'] ?? [];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/season/recent/team/stat: '.$exception->getMessage());
            info('/football/season/recent/team/stat: '.$seasonTeamStatisticData['code'].': '.$seasonTeamStatisticData['msg']);

            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
