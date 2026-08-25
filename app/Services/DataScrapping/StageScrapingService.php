<?php

namespace App\Services\DataScrapping;

use App\Models\Stage;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class StageScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Seasons from the sports api.
     */
    public function scrapStageData(array $params = []): array
    {
        $stageData = $this->callApi('/football/stage/list', $params);

        try {
            collect($stageData['results'] ?? [])
                ->filter(fn ($stage) => is_array($stage) && ScrapingCountryScope::seasonIdAllowed($stage['season_id'] ?? null))
                ->map(function ($stage) {
                    return [
                        'stage_id' => $stage['id'],
                        'season_id' => $stage['season_id'],
                        'name' => $stage['name'],
                        'mode' => $stage['mode'],
                        'group_count' => $stage['group_count'],
                        'round_count' => $stage['round_count'],
                        'order' => $stage['order'],
                        'updated_at' => Carbon::parse($stage['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($stageResults) {
                    Stage::query()
                        ->upsert($stageResults->toArray(), ['stage_id']);
                });

            return $stageData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/stage/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
