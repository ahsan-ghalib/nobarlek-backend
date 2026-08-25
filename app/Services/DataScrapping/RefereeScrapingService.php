<?php

namespace App\Services\DataScrapping;

use App\Models\Referee;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class RefereeScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Referees from the sports api.
     */
    public function scrapRefereeData(array $params = []): array
    {
        $refereeData = $this->callApi('/football/referee/list', $params);

        try {
            collect($refereeData['results'] ?? [])
                ->map(function ($referee) {
                    return [
                        'referee_id' => $referee['id'],
                        'name' => $referee['name'],
                        'logo' => $referee['logo'],
                        'birthday' => Carbon::parse($referee['birthday']),
                        'country_id' => $referee['country_id'],
                        'updated_at' => Carbon::parse($referee['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($refereeResults) {
                    Referee::query()
                        ->upsert($refereeResults->toArray(), ['referee_id']);
                });

            return $refereeData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/referee/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
