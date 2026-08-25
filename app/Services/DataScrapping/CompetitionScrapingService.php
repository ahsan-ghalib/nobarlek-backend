<?php

namespace App\Services\DataScrapping;

use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class CompetitionScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Competitions from the sports api.
     */
    public function scrapCompetitionData(array $params = []): array
    {
        $competitionData = $this->callApi('/football/competition/additional/list', $params);

        try {
            collect($competitionData['results'] ?? [])
                ->filter(fn ($competition) => is_array($competition) && ScrapingCountryScope::competitionAllowedFromApiRow($competition))
                ->map(function ($competition) {
                    return [
                        'competition_id' => $competition['id'],
                        'category_id' => $competition['category_id'],
                        'country_id' => $competition['country_id'],
                        'name' => $competition['name'],
                        'short_name' => $competition['short_name'],
                        'logo' => $competition['logo'],
                        'type' => $competition['type'],
                        'cur_season_id' => $competition['cur_season_id'],
                        'cur_stage_id' => $competition['cur_stage_id'],
                        'cur_round' => $competition['cur_round'],
                        'round_count' => $competition['round_count'],
                        'title_holder' => json_encode($competition['title_holder']),
                        'most_titles' => json_encode($competition['most_titles']),
                        'newcomers' => json_encode($competition['newcomers']),
                        'divisions' => json_encode($competition['divisions']),
                        'host' => json_encode($competition['host']),
                        'primary_color' => $competition['primary_color'],
                        'secondary_color' => $competition['secondary_color'],
                        'updated_at' => Carbon::parse($competition['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($competitionResults) {
                    Competition::query()
                        ->upsert($competitionResults->toArray(), ['competition_id']);
                });

            if (ScrapingCountryScope::shouldApply()) {
                ScrapingCountryScope::forgetCompetitionAndTeamCaches();
            }

            return $competitionData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/competition/additional/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }

    }
}
