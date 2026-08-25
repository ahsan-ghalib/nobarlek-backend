<?php

namespace App\Services\DataScrapping;

use App\Models\SeasonPlayerStatistics;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class SeasonPlayerStatisticsScrapingService
{
    use FootballScoreApiTrait;

    private array $params;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and insert/update the Newest Season Player Statistics from the sports api.
     */
    public function scrapNewestSeasonPlayerStatisticData(array $params = []): array
    {

        $this->params = $params;
        $seasonPlayerStatisticData = $this->callApi('/football/season/recent/player/stat', $params);

        return $this->upsertSeasonPlayerStatistics($seasonPlayerStatisticData);
    }

    /**
     * Scrap and insert/update the Season Player Statistics from the sports api.
     */
    public function scrapSeasonPlayerStatisticData(array $params = []): array
    {
        $this->params = $params;
        $seasonPlayerStatisticData = $this->callApi('/football/season/player/stat', $params);

        return $this->upsertSeasonPlayerStatistics($seasonPlayerStatisticData);
    }

    public function upsertSeasonPlayerStatistics(array $seasonPlayerStatisticData): array
    {
        try {
            if (ScrapingCountryScope::shouldApply() && isset($this->params['uuid']) && ! ScrapingCountryScope::seasonIdAllowed((string) $this->params['uuid'])) {
                return $seasonPlayerStatisticData['query'] ?? [];
            }

            if (count($seasonPlayerStatisticData['results'] ?? []) > 0) {
                $allowed = array_flip(array_merge(
                    (new SeasonPlayerStatistics)->getFillable(),
                    ['season_id', 'player_id', 'team_id', 'created_at', 'updated_at'],
                ));

                collect($seasonPlayerStatisticData['results'] ?? [])
                    ->map(function ($stats) use ($allowed) {
                        $stats = array_merge($stats, [
                            'player_id' => $stats['player']['id'],
                            'team_id' => $stats['team']['id'],
                            'season_id' => $this->params['uuid'],
                            'updated_at' => Carbon::parse($stats['updated_at']),
                        ]);
                        unset($stats['player'], $stats['team']);

                        return array_intersect_key($stats, $allowed);
                    })->chunk(200)->each(function ($chunk) {
                        SeasonPlayerStatistics::query()
                            ->upsert($chunk->toArray(), ['season_id', 'player_id', 'team_id']);
                    });
            }

            return $seasonPlayerStatisticData['query'] ?? [];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/season/recent/player/stat: '.$exception->getMessage());
            info('/football/season/recent/player/stat: '.($seasonPlayerStatisticData['code'] ?? '').': '.($seasonPlayerStatisticData['msg'] ?? ''));

            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
