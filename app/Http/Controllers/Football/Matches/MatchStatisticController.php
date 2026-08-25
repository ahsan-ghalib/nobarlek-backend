<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $matchTeamStatistics = $footballMatch->matchTeamStatistics()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->get();

        return $this->jsonResponse($matchTeamStatistics, Response::HTTP_OK);
    }
}
