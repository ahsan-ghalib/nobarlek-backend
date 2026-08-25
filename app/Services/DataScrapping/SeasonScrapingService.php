<?php

namespace App\Services\DataScrapping;

use App\Models\Season;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class SeasonScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Seasons from the sports api.
     */
    public function scrapSeasonData(array $params = []): array
    {
        $seasonData = $this->callApi('/football/season/list', $params);

        try {
            collect($seasonData['results'] ?? [])
                ->filter(fn ($season) => is_array($season) && ScrapingCountryScope::competitionIdAllowed($season['competition_id'] ?? null))
                ->map(function ($season) {
                    return [
                        'season_id' => $season['id'],
                        'competition_id' => $season['competition_id'],
                        'year' => (int) $season['year'],
                        'has_player_stats' => $season['has_player_stats'],
                        'has_team_stats' => $season['has_team_stats'],
                        'has_table' => $season['has_table'],
                        'is_current' => $season['is_current'],
                        'start_time' => Carbon::parse($season['start_time']),
                        'end_time' => Carbon::parse($season['end_time']),
                        'updated_at' => Carbon::parse($season['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($seasonResults) {
                    Season::query()
                        ->upsert($seasonResults->toArray(), ['season_id', 'competition_id']);
                });

            return $seasonData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/season/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
