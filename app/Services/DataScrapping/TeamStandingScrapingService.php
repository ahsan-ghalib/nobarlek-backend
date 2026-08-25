<?php

namespace App\Services\DataScrapping;

use App\Models\TeamStanding;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class TeamStandingScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update the Teams from the sports api.
     */
    public function scrapTeamStandingData(array $params = []): void
    {
        $teamsStandingData = $this->callApi('/football/table/live', $params);

        try {
            if (is_array($teamsStandingData) && ($teamsStandingData['code'] ?? -1) === 0) {
                foreach ($teamsStandingData['results'] ?? [] as $standingsDataTableResults) {
                    if (! is_array($standingsDataTableResults)) {
                        continue;
                    }
                    $seasonId = $standingsDataTableResults['season_id'] ?? '';
                    if (! ScrapingCountryScope::seasonIdAllowed($seasonId)) {
                        continue;
                    }
                    $this->insertTeamStandingData($standingsDataTableResults['tables'] ?? [], $seasonId);
                }
            }
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/table/live: '.$errorMessage);
        }
    }

    public function scrapHistoricalTeamStandingData(array $params): void
    {
        $teamsStandingData = $this->callApi('/football/season/table/detail', $params);

        if (($teamsStandingData['code'] ?? -1) !== 0) {
            info('/football/season/table/detail: '.($teamsStandingData['error'] ?? 'API code '.(string) ($teamsStandingData['code'] ?? 'unknown')));

            return;
        }

        try {
            if (is_array($teamsStandingData) && ($teamsStandingData['code'] ?? -1) === 0) {
                $seasonId = $params['uuid'] ?? '';
                if (ScrapingCountryScope::seasonIdAllowed($seasonId)) {
                    $tables = ($teamsStandingData['results'] ?? [])['tables'] ?? [];
                    $this->insertTeamStandingData($tables, $seasonId);
                }
            }
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/season/table/detail: '.$errorMessage);
        }

    }

    public function insertTeamStandingData(array $standingsTables, string $seasonId): void
    {
        foreach ($standingsTables as $standingsDataTable) {
            $standings = collect($standingsDataTable['rows'])
                ->map(function ($standings) use ($standingsDataTable, $seasonId) {
                    return array_merge($standings, [
                        'season_id' => $seasonId,
                        'standing_id' => $standingsDataTable['id'],
                        'conference' => $standingsDataTable['conference'] ?? '-',
                        'group' => $standingsDataTable['group'],
                        'stage_id' => $standingsDataTable['stage_id'],
                        'updated_at' => Carbon::parse($standings['updated_at']),
                    ]);
                })->toArray();

            TeamStanding::query()
                ->upsert($standings, ['team_id', 'standing_id', 'season_id']);
        }
    }
}
