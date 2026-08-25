<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Support\MatchTimeQuery;
use App\Support\ScrapingCountryScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamMatchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team, ?string $month = null)
    {
        $month = $month ?: now()->format('Y-m');

        $timezone = config('app.timezone');
        $start = Carbon::parse($month, $timezone)->startOfMonth()->timestamp;
        $end = Carbon::parse($month, $timezone)->endOfMonth()->timestamp;

        $teamMatches = MatchTimeQuery::applyBetween(
            FootballMatch::query()
                ->tap(fn ($q) => ScrapingCountryScope::restrictMatchesQuery($q))
                ->with(['competition', 'homeTeam', 'awayTeam'])
                ->where(function ($query) use ($team) {
                    $query->where('home_team_id', '=', $team->team_id)
                        ->orWhere('away_team_id', '=', $team->team_id);
                }),
            $start,
            $end,
        )
            ->get();

        return $this->jsonResponse($teamMatches, Response::HTTP_OK);
    }
}
