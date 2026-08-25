<?php

namespace App\Http\Controllers\Football\Teams;

use App\Enums\IncidentPositionEnum;
use App\Enums\MatchStateEnum;
use App\Enums\TechnicalStatisticsEnum;
use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\MatchIncidents;
use App\Models\SeasonTeamStatistics;
use App\Models\Team;
use App\Models\TeamSquad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamSquadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        $teamSquads = $team->squads()
            ->with([
                'player:id,player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,age,weight,height,country_id,market_value,market_value_currency' => [
                    'country:country_id,name,logo',
                ],
            ])
            ->get()
            ->map(function (TeamSquad $row) {
                $pos = trim((string) $row->position);

                return tap($row, function (TeamSquad $r) use ($pos) {
                    $r->position = $pos !== '' ? $pos : '—';
                });
            })
            ->groupBy('position');

        // Empty Collection JSON-encodes as [] and breaks clients expecting an object keyed by position.
        $squadsForJson = $teamSquads->isEmpty()
            ? new \stdClass()
            : $teamSquads;

        $topPlayers = [];

        $leagues = SeasonTeamStatistics::query()
            ->where('team_id', '=', $team->team_id)
            ->whereHas('season', fn (Builder $q) => $q->where('is_current', '=', 1))
            ->with(['season:season_id,competition_id,year' => ['competition:competition_id,name,logo']])
            ->get()
            ->map(fn ($row) => $row->season?->competition)
            ->filter()
            ->unique('competition_id')
            ->values();

        $goalDistributions = $this->buildGoalDistribution($team);

        $data = [
            'squads' => $squadsForJson,
            'top_players' => $topPlayers,
            'leagues' => $leagues,
            'goal_distributions' => $goalDistributions,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }

    private function buildGoalDistribution(Team $team): array
    {
        $seasonId = $team->competition?->cur_season_id;
        if (!$seasonId) {
            return [];
        }

        $matches = FootballMatch::query()
            ->select(['match_id', 'home_team_id', 'away_team_id'])
            ->where('season_id', '=', $seasonId)
            ->where('status_id', '=', MatchStateEnum::END->value)
            ->where(function ($q) use ($team) {
                $q->where('home_team_id', '=', $team->team_id)
                    ->orWhere('away_team_id', '=', $team->team_id);
            })
            ->get();

        if ($matches->isEmpty()) {
            return [];
        }

        $matchMap = $matches->keyBy('match_id');

        $goalTypes = [
            TechnicalStatisticsEnum::GOAL->value,
            TechnicalStatisticsEnum::PENALTY->value,
            TechnicalStatisticsEnum::OWN_GOAL->value,
        ];

        $incidents = MatchIncidents::query()
            ->select(['match_id', 'type', 'position', 'time'])
            ->whereIn('match_id', $matches->pluck('match_id'))
            ->whereIn('type', $goalTypes)
            ->get();

        $init = fn () => [
            'matches' => 0,
            'scored' => [0, 0, 0, 0, 0, 0],
            'conceded' => [0, 0, 0, 0, 0, 0],
        ];

        $out = [
            'all' => $init(),
            'home' => $init(),
            'away' => $init(),
        ];

        $out['all']['matches'] = $matches->count();
        $out['home']['matches'] = $matches->where('home_team_id', '=', $team->team_id)->count();
        $out['away']['matches'] = $matches->where('away_team_id', '=', $team->team_id)->count();

        $bucket = function (int $minute): int {
            if ($minute <= 15) return 0;
            if ($minute <= 30) return 1;
            if ($minute <= 45) return 2;
            if ($minute <= 60) return 3;
            if ($minute <= 75) return 4;
            return 5;
        };

        foreach ($incidents as $inc) {
            $match = $matchMap->get($inc->match_id);
            if (!$match) continue;

            $pos = (string) $inc->position;
            if ($pos !== IncidentPositionEnum::HOME_TEAM->value && $pos !== IncidentPositionEnum::AWAY_TEAM->value) {
                continue;
            }

            $homeId = (string) $match->home_team_id;
            $awayId = (string) $match->away_team_id;
            $sideTeamId = $pos === IncidentPositionEnum::HOME_TEAM->value ? $homeId : $awayId;
            $scorerId = $sideTeamId;

            if ((string) $inc->type === TechnicalStatisticsEnum::OWN_GOAL->value) {
                $scorerId = $sideTeamId === $homeId ? $awayId : $homeId;
            }

            $minute = (int) ($inc->time ?? 0);
            $idx = $bucket($minute);

            $key = $homeId === $team->team_id ? 'home' : ($awayId === $team->team_id ? 'away' : 'all');

            foreach (['all', $key] as $group) {
                if ($scorerId === $team->team_id) {
                    $out[$group]['scored'][$idx] += 1;
                } else {
                    $out[$group]['conceded'][$idx] += 1;
                }
            }
        }

        return $out;
    }
}
