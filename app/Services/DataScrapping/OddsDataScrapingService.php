<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\OddData;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class OddsDataScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Categories from the sports api.
     */
    public function scrapOddsData(array $params = []): void
    {
        $oddsData = $this->callApi('/football/odds/live', $params);
        try {
            if (($oddsData['code'] ?? null) === 0) {
                collect($oddsData['results'] ?? [])
                    ->map(function ($odds, $key) {
                        if (in_array($key, [2, 3, 4, 9])) {
                            $this->insertOdds($odds, $key);
                        }
                    })
                    ->toArray();
            }

        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/odds/live: '.$exception->getMessage());
        }
    }

    private function insertOdds(array $companyOdds, $companyId): void
    {
        $rows = collect($companyOdds)
            ->filter(fn ($companyOdd) => is_array($companyOdd) && isset($companyOdd[0]))
            ->transform(fn ($companyOdd) => [
                'company_id' => $companyId,
                'match_id' => $companyOdd[0],
                'type' => $companyOdd[1] ?? 0,
                'change_time' => (int) ($companyOdd[2][0] ?? 0),
                'match_time' => (int) ($companyOdd[2][1] ?? 0),
                'home_draw_away' => $companyOdd[2][2] ?? '',
                'match_status' => $companyOdd[2][3] ?? 0,
                'goal_score' => $companyOdd[3] ?? [],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        if (ScrapingCountryScope::shouldApply()) {
            $mids = $rows->pluck('match_id')->unique()->filter()->values()->all();
            $compByMatch = $mids === [] ? collect()
                : FootballMatch::query()->whereIn('match_id', $mids)->pluck('competition_id', 'match_id');
            $rows = $rows->filter(fn ($r) => ScrapingCountryScope::competitionIdAllowed($compByMatch[$r['match_id']] ?? null));
        }

        $companyOdds = $rows->values()->toArray();

        if ($companyOdds === []) {
            return;
        }

        OddData::query()
            ->insert($companyOdds);

    }
}
