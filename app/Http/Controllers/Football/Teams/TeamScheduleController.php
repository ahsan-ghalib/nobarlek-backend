<?php

namespace App\Http\Controllers\Football\Teams;

use App\Enums\MatchScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Repositories\FootballMatchRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamScheduleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team, ?string $date = null)
    {
        $month = $date !== null && $date !== '' ? $date : now()->format('Y-m');

        $matchSchedules = (new FootballMatchRepository())->getSingleTeamMatches($team, MatchScheduleStatusEnum::Schedule, $month)
            ->get();

        $data = collect([
            'match_schedules' => $matchSchedules,
        ]);

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
