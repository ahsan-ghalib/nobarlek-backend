<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\SeasonTeamStatistics;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamStatisticsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        $teamStatistics = SeasonTeamStatistics::query()
            ->where('team_id', '=', $team->team_id)
            ->whereHas('season', fn (Builder $query) => $query->where('is_current', '=', 1))
            ->with([
                'season:season_id,competition_id,year' => [
                    'competition:competition_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                ],
            ])
            ->get();

        return $this->jsonResponse($teamStatistics, Response::HTTP_OK);
    }
}
