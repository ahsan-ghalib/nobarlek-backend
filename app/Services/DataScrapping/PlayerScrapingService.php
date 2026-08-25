<?php

namespace App\Services\DataScrapping;

use App\Models\Player;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class PlayerScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Players from the sports api.
     */
    public function scrapPlayerData(array $params = []): array
    {
        $playerData = $this->callApi('/football/player/with_stat/list', $params);

        try {
            collect($playerData['results'] ?? [])
                // ->filter(fn ($player) => is_array($player) && ScrapingCountryScope::teamIdAllowed($player['team_id'] ?? null))
                ->transform(function ($player) {
                    return [
                        'player_id' => $player['id'],
                        'team_id' => $player['team_id'],
                        'name' => $player['name'],
                        'short_name' => $player['short_name'],
                        'logo' => $player['logo'],
                        'national_logo' => $player['national_logo'],
                        'age' => $player['age'],
                        'birthday' => $player['birthday'],
                        'weight' => $player['weight'],
                        'height' => $player['height'],
                        'country_id' => $player['country_id'],
                        'nationality' => $player['nationality'],
                        'market_value' => $player['market_value'],
                        'market_value_currency' => $player['market_value_currency'],
                        'contract_until' => $player['contract_until'],
                        'preferred_foot' => $player['preferred_foot'],
                        'ability' => json_encode($player['ability']),
                        'characteristics' => json_encode($player['characteristics']),
                        'position' => $player['position'],
                        'positions' => json_encode($player['positions']),
                        'updated_at' => Carbon::parse($player['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($playerResults) {
                    Player::query()
                        ->upsert($playerResults->toArray(), ['player_id']);
                    unset($playerResults);
                });

            $query = $playerData['query'];
            unset($playerData);

            return $query;
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/player/with_stat/list: '.$errorMessage);

            return ['error' => $errorMessage];
        }

    }
}
