<?php

namespace App\Http\Controllers\Football\Teams;

use App\Enums\MatchScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Repositories\FootballMatchRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamScheduleStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team, MatchScheduleStatusEnum $statusEnum)
    {
        $matchSchedules = (new FootballMatchRepository())->getSingleTeamMatches($team, $statusEnum, $request->month);

        return $this->jsonResponse(collect($matchSchedules), Response::HTTP_OK);
    }
}
