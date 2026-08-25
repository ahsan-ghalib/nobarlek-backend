<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamStanding;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamStandingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request, Team $team)
    {
        $competition = $team->competition;

        $teamStandings = TeamStanding::query()
            ->with(['team:id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo'])
            ->where('season_id', '=', $competition->cur_season_id)
            ->where('stage_id', '=', $competition->cur_stage_id)
            ->orderByDesc('points')
            ->get();

        return $this->jsonResponse(collect([
            'competition_name' => $competition->name,
            'team_standings' => $teamStandings,
            'season' => $competition->currentSeason,
        ]), Response::HTTP_OK);
    }
}
