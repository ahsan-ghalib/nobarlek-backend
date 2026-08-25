<?php

namespace App\Services\DataScrapping;

use App\Models\Venue;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class VenueScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Venues from the sports api.
     */
    public function scrapVenueData(array $params = []): array
    {
        $venueData = $this->callApi('/football/venue/list', $params);

        try {
            collect($venueData['results'] ?? [])
                ->map(function ($coach) {
                    return [
                        'venue_id' => $coach['id'],
                        'name' => $coach['name'],
                        'capacity' => $coach['capacity'],
                        'country_id' => $coach['country_id'],
                        'city' => $coach['city'],
                        'country' => $coach['country'],
                        'updated_at' => Carbon::parse($coach['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($venueResults) {
                    Venue::query()
                        ->upsert($venueResults->toArray(), ['venue_id']);
                });

            return $venueData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/venue/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }

    }
}
