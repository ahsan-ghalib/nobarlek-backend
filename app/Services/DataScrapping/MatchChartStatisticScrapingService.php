<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\MatchChartStatistic;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class MatchChartStatisticScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Match Player Statistics from the sports api.
     *
     * @param  array<string, mixed>  $params
     */
    public function scrapMatchChartStatisticData(array $params = []): ?array
    {
        $matchId = isset($params['uuid']) ? (string) $params['uuid'] : '';
        if (ScrapingCountryScope::shouldApply() && $matchId !== '') {
            $cid = FootballMatch::query()->where('match_id', $matchId)->value('competition_id');
            if (! ScrapingCountryScope::competitionIdAllowed($cid !== null ? (string) $cid : null)) {
                return null;
            }
        }

        $matchChartStatisticData = $this->callApi('/football/match/trend/detail', $params);

        try {
            if (! is_array($matchChartStatisticData)
                || (int) ($matchChartStatisticData['code'] ?? -1) !== 0
                || ! is_array($matchChartStatisticData['results'] ?? null)
                || count($matchChartStatisticData['results']) === 0) {
                return null;
            }

            $results = $matchChartStatisticData['results'];
            MatchChartStatistic::query()
                ->updateOrCreate(['match_id' => $params['uuid']], [
                    'count' => $results['count'] ?? null,
                    'per' => $results['per'] ?? null,
                    // The model casts timeline to an array. Passing the array directly
                    // prevents it from being stored as a double-encoded JSON string.
                    'timeline' => $results['data'] ?? [],
                ]);

            return null;
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('football/match/trend/detail:'.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
