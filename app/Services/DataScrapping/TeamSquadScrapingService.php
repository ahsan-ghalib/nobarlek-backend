<?php

namespace App\Services\DataScrapping;

use App\Models\TeamSquad;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class TeamSquadScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Teams from the sports api.
     */
    public function scrapTeamSquadData(array $params = []): array
    {
        $teamSquadsData = $this->callApi('/football/team/squad/list', $params);

        try {
            $teamSquads = collect($teamSquadsData['results'] ?? [])
                ->filter(fn ($teamSquad) => is_array($teamSquad) && ScrapingCountryScope::teamIdAllowed($teamSquad['id'] ?? null))
                ->map(function ($teamSquad) {
                    return collect($teamSquad['squad'])
                        ->map(function ($squad) use ($teamSquad) {
                            return [
                                'team_id' => $teamSquad['id'],
                                'player_id' => $squad['player']['id'],
                                'position' => $squad['position'],
                                'shirt_number' => $squad['shirt_number'],
                            ];
                        });
                })
                ->flatten(1)
                ->toArray();

            TeamSquad::query()
                ->upsert($teamSquads, ['team_id', 'player_id']);

            return $teamSquadsData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/team/squad/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }
    }
}
