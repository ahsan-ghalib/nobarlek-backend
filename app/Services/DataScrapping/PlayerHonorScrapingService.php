<?php

namespace App\Services\DataScrapping;

use App\Models\PlayerHonor;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class PlayerHonorScrapingService
{
    use FootballScoreApiTrait;

    /**
     * @param  array<string, mixed>  $honor
     */
    private static function normalizeSeasonId(array $honor): string
    {
        $seasonId = trim((string) ($honor['season_id'] ?? ''));
        if ($seasonId !== '' && $seasonId !== '-') {
            return $seasonId;
        }

        $season = trim((string) ($honor['season'] ?? ''));
        if ($season !== '' && $season !== '-') {
            return 'label:'.$season;
        }

        return '-';
    }

    /**
     * Scrap and inset/update the Player Honors from the sports api.
     */
    public function scrapPlayerHonorsData(array $params = []): array
    {
        $playerSalariesData = $this->callApi('/football/player/honor/list', $params);

        if (! is_array($playerSalariesData)) {
            return ['error' => 'Request failed', 'total' => 0];
        }

        try {
            if (($playerSalariesData['query']['total'] ?? 0) > 0 && is_array($playerSalariesData['results'] ?? null)) {
                collect($playerSalariesData['results'] ?? [])
                    ->filter(fn ($player) => is_array($player))
                    ->map(function ($player) {
                        return collect($player['honors'] ?? [])->map(function ($honor) use ($player) {
                            return [
                                'player_id' => $player['id'],
                                'honor_id' => $honor['honor']['id'] ?? '-',
                                'season' => $honor['season'] ?? '-',
                                'competition_id' => $honor['competition_id'] ?? '-',
                                'season_id' => self::normalizeSeasonId($honor),
                                'updated_at' => Carbon::parse($player['updated_at']),
                                'created_at' => now(),
                            ];
                        });
                    })
                    ->flatten(1)
                    ->filter(function ($row) {
                        if (! ScrapingCountryScope::shouldApply()) {
                            return true;
                        }
                        $cid = (string) ($row['competition_id'] ?? '');

                        return $cid !== '' && $cid !== '-' && ScrapingCountryScope::competitionIdAllowed($cid);
                    })
                    ->unique(fn (array $row) => $row['player_id'].'|'.$row['honor_id'].'|'.$row['season_id'])
                    ->values()
                    ->chunk(100)
                    ->each(function ($playerSalariesChunk) {
                        PlayerHonor::query()->upsert(
                            $playerSalariesChunk->toArray(),
                            ['player_id', 'honor_id', 'season_id'],
                            ['season', 'competition_id', 'updated_at'],
                        );
                    });
            }

            return $playerSalariesData['query'];
        } catch (\Exception $exception) {
            info('/football/player/honor/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
                'total' => 0,
            ];
        }

    }
}
