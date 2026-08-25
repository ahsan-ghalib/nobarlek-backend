<?php

namespace App\Services\DataScrapping;

use App\Models\Honor;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class HonorScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Honors from the sports api.
     */
    public function scrapHonorData(array $params = []): array
    {
        $honorsData = $this->callApi('/football/honor/list', $params);

        try {
            $honors = collect($honorsData['results'] ?? [])
                ->transform(function ($honor) {
                    return [
                        'honor_id' => $honor['id'],
                        'name' => $honor['name'],
                        'logo' => $honor['logo'],
                        'updated_at' => Carbon::parse($honor['updated_at']),
                    ];
                })
                ->toArray();
            Honor::query()
                ->upsert($honors, ['honor_id']);

            return $honorsData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/honor/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
