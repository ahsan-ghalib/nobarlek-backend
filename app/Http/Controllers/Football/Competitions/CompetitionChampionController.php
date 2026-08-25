<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionChampionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition)
    {
        $teamHonors = $competition->teamHonors()
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'honor:honor_id,name,logo',
            ])
            ->orderByDesc('season')
            ->get();

        return $this->jsonResponse($teamHonors, Response::HTTP_OK);
    }
}
