<?php

namespace App\Services\DataScrapping;

use App\Models\Team;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class TeamScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Teams from the sports api.
     */
    public function scrapTeamData(array $params = []): array
    {
        $teamsData = $this->callApi('/football/team/additional/list', $params);

        try {
            collect($teamsData['results'] ?? [])
                // ->filter(function ($team) {
                //     if (! is_array($team)) {
                //         return false;
                //     }
                //     if (! ScrapingCountryScope::shouldApply()) {
                //         return true;
                //     }
                //     $national = (int) ($team['national'] ?? 0) === 1;
                //     if ($national) {
                //         return in_array($team['country_id'] ?? '', ScrapingCountryScope::resolvedCountryIds(), true);
                //     }

                //     return ScrapingCountryScope::competitionIdAllowed($team['competition_id'] ?? null);
                // })
                ->map(function ($team) {
                    return [
                        'team_id' => $team['id'],
                        'competition_id' => $team['competition_id'],
                        'country_id' => $team['country_id'],
                        'name' => $team['name'],
                        'short_name' => $team['short_name'],
                        'logo' => $team['logo'],
                        'national' => $team['national'],
                        'country_logo' => $team['country_logo'],
                        'foundation_time' => $team['foundation_time'],
                        'website' => $team['website'],
                        'coach_id' => $team['coach_id'],
                        'venue_id' => $team['venue_id'],
                        'market_value' => $team['market_value'],
                        'market_value_currency' => $team['market_value_currency'],
                        'total_players' => $team['total_players'],
                        'foreign_players' => $team['foreign_players'],
                        'national_players' => $team['national_players'],
                        'updated_at' => Carbon::parse($team['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($teamResults) {
                    Team::query()
                        ->upsert($teamResults->toArray(), ['team_id']);
                });

            return $teamsData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/team/additional/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
