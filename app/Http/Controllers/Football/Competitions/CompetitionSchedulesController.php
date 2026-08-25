<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitonScheduleRequest;
use App\Models\Competition;
use App\Models\Season;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionSchedulesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CompetitonScheduleRequest $request, Competition $competition, Season $season)
    {
        $matches = $competition->matches()
            ->with([
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,logo',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,logo',
                'matchInformation',
            ])
            ->latest('match_time')
            ->where('stage_id', '=', $request->stage_id)
            ->when(isset($season->season_id), fn ($query) => $query->where('season_id', '=', $season->season_id),
                fn ($query) => $query->where('season_id', '=', $competition->cur_season_id)
            )
            ->when(isset($request->round) && $request->round > 0, fn ($query) => $query->where('round_num', '=', $request->round))
            ->when(isset($request->group) && $request->group !== 'All' && $request->group > 0, fn ($query) => $query->where('group_num', '=', $request->group))
            ->get();

        return $this->jsonResponse($matches, Response::HTTP_OK);

    }
}
