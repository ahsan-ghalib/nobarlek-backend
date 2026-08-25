<?php

namespace App\Services\DataScrapping;

use App\Models\Country;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class CountryScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Countries from the sports api.
     */
    public function scrapCountryData(array $params = []): void
    {
        $countriesData = $this->callApi('/football/country/list', $params);

        try {
            $countriesData = collect($countriesData['results'] ?? [])
                ->transform(function ($country) {
                    return [
                        'country_id' => $country['id'],
                        'category_id' => $country['category_id'],
                        'name' => $country['name'],
                        'logo' => $country['logo'],
                        'updated_at' => Carbon::parse($country['updated_at']),
                    ];
                })
                ->toArray();
            Country::query()
                ->upsert($countriesData, ['country_id', 'category_id']);
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/country/list: '.$exception->getMessage());
        }

    }
}
