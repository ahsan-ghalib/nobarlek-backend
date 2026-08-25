<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\SeasonTopScorer;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamTopScorerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team): JsonResponse
    {
        $topScorers = SeasonTopScorer::query()
            ->where('season_id', '=', $team->competition->cur_season_id)
            ->where('team_id', '=', $team->team_id)
            ->with('player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo')
            ->get();

        return $this->jsonResponse($topScorers, Response::HTTP_OK);
    }
}
