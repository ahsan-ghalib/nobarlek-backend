<?php

namespace App\Services\DataScrapping;

use App\Models\TeamHonor;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class TeamHonorScrapingService
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
     * Scrap and inset/update the team honors from the sports api.
     */
    public function scrapTeamHonorsData(array $params = []): array
    {
        $teamHonorsData = $this->callApi('/football/team/honor/list', $params);

        if (! is_array($teamHonorsData)) {
            return ['error' => 'Request failed', 'total' => 0];
        }

        try {
            if (($teamHonorsData['query']['total'] ?? 0) > 0 && is_array($teamHonorsData['results'] ?? null)) {
                collect($teamHonorsData['results'] ?? [])
                    ->filter(fn ($team) => is_array($team)
                        && (! ScrapingCountryScope::shouldApply() || ScrapingCountryScope::teamIdAllowed((string) ($team['id'] ?? ''))))
                    ->map(function ($team) {
                        return collect($team['honors'] ?? [])->map(function ($honor) use ($team) {
                            return [
                                'team_id' => $team['id'],
                                'honor_id' => $honor['honor']['id'] ?? '-',
                                'season' => $honor['season'] ?? '-',
                                'competition_id' => $honor['competition_id'] ?? '-',
                                'season_id' => self::normalizeSeasonId($honor),
                                'updated_at' => Carbon::parse($team['updated_at']),
                                'created_at' => now(),
                            ];
                        });
                    })
                    ->flatten(1)
                    ->unique(fn (array $row) => $row['team_id'].'|'.$row['honor_id'].'|'.$row['season_id'])
                    ->values()
                    ->chunk(100)
                    ->each(function ($teamHonorsChunk) {
                        TeamHonor::query()->upsert(
                            $teamHonorsChunk->toArray(),
                            ['team_id', 'honor_id', 'season_id'],
                            ['season', 'competition_id', 'updated_at'],
                        );
                    });
            }

            return $teamHonorsData['query'];
        } catch (\Exception $exception) {
            info('/football/team/honor/list: '.$exception->getMessage());

            return [
                'error' => $exception->getMessage(),
                'total' => 0,
            ];
        }

    }
}
