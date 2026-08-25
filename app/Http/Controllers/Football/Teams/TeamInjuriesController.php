<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInjury;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamInjuriesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        $teamStatistics = TeamInjury::query()
            ->where('team_id', '=', $team->team_id)
            ->with([
                'player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,position,age',
            ])
            ->get();

        return $this->jsonResponse($teamStatistics, Response::HTTP_OK);
    }
}
