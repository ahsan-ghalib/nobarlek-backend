<?php

namespace App\Services\DataScrapping;

use App\Models\Coach;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class CoachScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Coaches from the sports api.
     */
    public function scrapCoachData(array $params = []): array
    {
        $coachesData = $this->callApi('/football/coach/list', $params);
        try {
            collect($coachesData['results'] ?? [])
                ->filter(fn ($coach) => is_array($coach)
                    && (! ScrapingCountryScope::shouldApply() || ScrapingCountryScope::teamIdAllowed($coach['team_id'] ?? null)))
                ->map(function ($coach) {
                    return [
                        'coach_id' => $coach['id'],
                        'team_id' => $coach['team_id'],
                        'name' => $coach['name'],
                        'logo' => $coach['logo'],
                        'age' => $coach['age'],
                        'birthday' => $coach['birthday'],
                        'preferred_formation' => $coach['preferred_formation'],
                        'country_id' => $coach['country_id'],
                        'nationality' => $coach['nationality'],
                        'updated_at' => Carbon::parse($coach['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($coachesResults) {
                    Coach::query()
                        ->upsert($coachesResults->toArray(), ['coach_id']);
                });

            return $coachesData['query'] ?? [];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/coach/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
            ];
        }

    }
}
