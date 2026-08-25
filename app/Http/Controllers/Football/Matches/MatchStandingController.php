<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionStandingResource;
use App\Models\FootballMatch;
use App\Models\SeasonTopScorer;
use App\Models\TeamStanding;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchStandingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $matchStandings = TeamStanding::query()
            ->with([
                'team:team_id,name,logo',
            ])
            ->where('season_id', '=', $footballMatch->season_id)
            ->where('stage_id', '=', $footballMatch->stage_id)
            ->orderByDesc('points')
            ->get();

        $standingRequest = $request->duplicate(['type' => $request->query('type', 'All')]);
        $matchStandings = CompetitionStandingResource::collection($matchStandings)
            ->toArray($standingRequest);

        $topScorer = SeasonTopScorer::query()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->where('season_id', '=', $footballMatch->season_id)
            ->limit(50)
            ->orderByDesc('goals')
            ->get();

        $competition = $footballMatch->competition;

        $data = [
            'competition' => $competition,
            'match_standings' => $matchStandings,
            'top_scorer' => $topScorer,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
