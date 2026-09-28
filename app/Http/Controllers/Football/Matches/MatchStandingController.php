<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionStandingResource;
use App\Models\FootballMatch;
use App\Models\TeamStanding;
use App\Support\SeasonPlayerLeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchStandingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $seasonStandings = fn () => TeamStanding::query()
            ->with([
                'team:team_id,name,logo',
            ])
            ->where('season_id', '=', $footballMatch->season_id)
            ->orderByDesc('points');

        // Prefer the match's own stage; many matches carry no stage_id, or the table
        // is stored under another stage, so fall back to the whole season.
        $matchStandings = filled($footballMatch->stage_id)
            ? $seasonStandings()->where('stage_id', '=', $footballMatch->stage_id)->get()
            : collect();

        if ($matchStandings->isEmpty() && filled($footballMatch->season_id)) {
            $matchStandings = $seasonStandings()->get();
        }

        $standingRequest = $request->duplicate(['type' => $request->query('type', 'All')]);
        $matchStandings = CompetitionStandingResource::collection($matchStandings)
            ->toArray($standingRequest);

        $topScorer = SeasonPlayerLeaders::topScorers($footballMatch->season_id, 50);

        $competition = $footballMatch->competition;

        $data = [
            'competition' => $competition,
            'match_standings' => $matchStandings,
            'top_scorer' => $topScorer,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
