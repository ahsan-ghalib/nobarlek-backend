<?php

namespace App\Services\DataScrapping;

use App\Enums\InjuryTypeEnum;
use App\Models\TeamInjury;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class TeamInjurycrapingService
{
    use FootballScoreApiTrait;

    private array $injuryTypes;

    public function __construct()
    {
        ini_set('memory_limit', -1);
        $this->injuryTypes = InjuryTypeEnum::values();
    }

    /**
     * Scrap and inset/update the Teams from the sports api.
     */
    public function scrapTeamInjuryData(array $params = []): array
    {
        $teamInjuriesData = $this->callApi('/football/team/injury/list', $params);

        try {
            if (! is_array($teamInjuriesData) || ($teamInjuriesData['code'] ?? -1) !== 0) {
                return is_array($teamInjuriesData) ? ($teamInjuriesData['query'] ?? []) : [];
            }

            $teamInjuries = collect($teamInjuriesData['results'] ?? [])
                ->filter(fn ($teamInjury) => is_array($teamInjury)
                    && (! ScrapingCountryScope::shouldApply() || ScrapingCountryScope::teamIdAllowed($teamInjury['id'] ?? null)))
                ->map(function ($teamInjury) {
                    return collect($teamInjury['injury'] ?? [])
                        ->map(function ($injury) use ($teamInjury) {
                            return [
                                'competition_id' => $injury['competition_id'],
                                'team_id' => $teamInjury['id'],
                                'player_id' => $injury['player_id'],
                                'type' => $this->injuryTypes[$injury['type']],
                                'injury_id' => $injury['injury_id'],
                                'reason' => $injury['reason'],
                                'start_time' => $injury['start_time'],
                                'end_time' => $injury['end_time'],
                                'missed_matches' => $injury['missed_matches'],
                                'updated_at' => Carbon::parse($teamInjury['updated_at']),
                            ];
                        });
                })
                ->flatten(1)
                ->toArray();

            if ($teamInjuries !== []) {
                TeamInjury::query()
                    ->upsert($teamInjuries, ['team_id', 'player_id', 'competition_id']);
            }

            return $teamInjuriesData['query'] ?? [];
        } catch (\Exception $exception) {
            info('/football/team/injury/list: '.$exception->getMessage());

            return ['error' => $exception->getMessage()];
        }
    }
}
