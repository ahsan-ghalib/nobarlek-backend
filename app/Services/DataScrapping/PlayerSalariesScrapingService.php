<?php

namespace App\Services\DataScrapping;

use App\Models\PlayerSalary;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class PlayerSalariesScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Player salaries from the sports api.
     */
    public function scrapPlayerSalariesData(array $params = []): array
    {
        $playerSalariesData = $this->callApi('/football/player/salary/list', $params);

        try {
            if (($playerSalariesData['query']['total'] ?? 0) > 0) {
                collect($playerSalariesData['results'] ?? [])
                    ->filter(fn ($salaries) => is_array($salaries)
                        && (! ScrapingCountryScope::shouldApply() || ScrapingCountryScope::playerIdAllowed((string) ($salaries['id'] ?? ''))))
                    ->map(function ($salaries) {
                        return collect($salaries['salary'])->map(function ($salary) use ($salaries) {
                            return array_merge($salary, ['player_id' => $salaries['id'], 'updated_at' => Carbon::parse($salaries['updated_at'])]);
                        });
                    })
                    ->flatten(1)
                    ->chunk(100)
                    ->map(function ($playerSalariesChunk) {
                        PlayerSalary::query()
                            ->upsert($playerSalariesChunk->toArray(), ['player_id', 'team_id', 'season']);
                    });
            }

            return $playerSalariesData['query'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/player/salary/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
            ];
        }

    }
}
