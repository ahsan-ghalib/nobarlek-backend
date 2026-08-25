<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Support\ScrapingCountryScope;
use Symfony\Component\HttpFoundation\Response;

class PlayerMatchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Player $player)
    {
        $playerMatches = FootballMatch::query()
            ->tap(fn ($q) => ScrapingCountryScope::restrictMatchesQuery($q))
            ->select([
                'match_time',
                'match_id',
                'status_id',
                'kickoff_time',
                'home_scores',
                'away_scores',
                'home_team_id',
                'away_team_id',
                'competition_id',
            ])
            ->withWhereHas('matchPlayerStatistics', fn ($query) => $query->select(['match_id', 'minutes_played',
                'goals',
                'assists',
                'rating'])->where('player_id', '=', $player->player_id))
            ->with(['competition', 'homeTeam', 'awayTeam'])
            ->latest('match_time')
            ->get();

        return $this->jsonResponse($playerMatches, Response::HTTP_OK);
    }
}
