<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionStandingResource;
use App\Models\Competition;
use App\Models\Season;
use App\Models\SeasonPlayerStatistics;
use App\Models\SeasonTopScorer;
use App\Models\TeamStanding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CompetitionStandingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function teamStandings(Request $request, Competition $competition, Season $season): JsonResponse
    {
        $seasonId = $this->standingsSeasonId($competition, $season);

        $teamStandings = TeamStanding::query()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->where('season_id', $seasonId)
//            ->where('stage_id', '=', $competition->cur_stage_id)
            ->orderByDesc('points')
            ->limit(5)
            ->get();

        return $this->jsonResponse($teamStandings, Response::HTTP_OK);
    }

    public function fullTeamStandings(Request $request, Competition $competition, Season $season): JsonResponse
    {
        $seasonId = $this->standingsSeasonId($competition, $season);

        $teamStandings = TeamStanding::query()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->where('season_id', $seasonId)
//            ->where('stage_id', '=', $competition->cur_stage_id)
            ->when($request->type === 'home', fn ($query) => $query->orderBy('home_position'))
            ->when($request->type === 'away', fn ($query) => $query->orderBy('away_position'))
            ->when(! in_array($request->type, ['home', 'away'], true), fn ($query) => $query->orderBy('position'))
            ->get();

        $teamStandings = CompetitionStandingResource::collection($teamStandings)->resolve();

        return $this->jsonResponse($this->groupStandings(collect($teamStandings)), Response::HTTP_OK);
    }

    public function playerStandings(Request $request, Competition $competition, Season $season): JsonResponse
    {
        $seasonId = $this->standingsSeasonId($competition, $season);

        $topScorer = SeasonTopScorer::query()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->where('season_id', $seasonId)
            ->limit(10)
            ->orderByDesc('goals')
            ->get();

        // The shooter-stat feed is not available for every competition. The
        // player-stat feed contains the same scorer fields and is already
        // populated for those seasons, so do not return an empty tab.
        if ($topScorer->isEmpty()) {
            $topScorer = SeasonPlayerStatistics::query()
                ->with([
                    'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                    'player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                ])
                ->where('season_id', $seasonId)
                ->orderByDesc('goals')
                ->limit(10)
                ->get();
        }

        return $this->jsonResponse($topScorer, Response::HTTP_OK);
    }

    private function standingsSeasonId(Competition $competition, Season $season): ?string
    {
        if (! isset($season->season_id)) {
            return $competition->cur_season_id;
        }

        abort_unless(
            $season->competition_id === $competition->competition_id,
            Response::HTTP_NOT_FOUND
        );

        return $season->season_id;
    }

    /**
     * Keep genuine competition groups separate, but give conventional league
     * tables a stable section name instead of exposing a blank/default group.
     *
     * @param  Collection<int, array<string, mixed>>  $standings
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    private function groupStandings(Collection $standings): Collection
    {
        $meaningfulGroups = $standings
            ->pluck('group')
            ->map(fn ($group) => trim((string) $group))
            ->reject(fn (string $group) => $group === '' || $group === '0')
            ->unique()
            ->values();

        if ($meaningfulGroups->count() <= 1) {
            return collect(['Standings' => $standings->values()]);
        }

        return $standings
            ->groupBy(fn (array $standing) => trim((string) $standing['group']))
            ->map(fn (Collection $group) => $group->values());
    }
}
