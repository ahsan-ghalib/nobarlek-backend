<?php

namespace App\Services\DataScrapping;

use App\Models\CoachHonor;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class CoachHonorScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Coach Honors from the sports api.
     */
    public function scrapCoachHonorsData(array $params = []): array
    {
        $coachHonorsData = $this->callApi('/football/coach/honor/list', $params);
        try {
            if (($coachHonorsData['query']['total'] ?? 0) > 0) {
                collect($coachHonorsData['results'] ?? [])
                    ->filter(fn ($coach) => is_array($coach))
                    ->map(function ($coach) {
                        return collect($coach['honors'] ?? [])->map(function ($honor) use ($coach) {
                            return [
                                'coach_id' => $coach['id'],
                                'honor_id' => $honor['honor']['id'] ?? '-',
                                'season' => $honor['season'] ?? '-',
                                'competition_id' => $honor['competition_id'] ?? '-',
                                'season_id' => $honor['season_id'] ?? '-',
                                'updated_at' => Carbon::parse($coach['updated_at']),
                            ];
                        });
                    })
                    ->flatten(1)
                    ->filter(function ($row) {
                        if (! ScrapingCountryScope::shouldApply()) {
                            return true;
                        }
                        $cid = (string) ($row['competition_id'] ?? '');

                        return $cid !== '' && $cid !== '-' && ScrapingCountryScope::competitionIdAllowed($cid);
                    })
                    ->chunk(100)
                    ->map(function ($coachHonorsChunk) {
                        CoachHonor::query()
                            ->upsert($coachHonorsChunk->toArray(), ['coach_id', 'honor_id', 'season_id']);
                    });
            }

            return $coachHonorsData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/coach/honor/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
