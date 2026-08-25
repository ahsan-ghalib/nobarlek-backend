<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionSeasonController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition)
    {
        $competitionSeason = $competition->seasons()
            ->select(['season_id', 'year', 'is_current', 'start_time', 'end_time'])
            // ->whereHas('teamStandings')
            ->orderByDesc('is_current')
            ->orderByDesc('start_time')
            ->orderByDesc('year')
            ->get();

        return $this->jsonResponse($competitionSeason, Response::HTTP_OK);
    }
}
