<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeasonPlayerStatisticController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        $seasonPlayerStatistics = $player->seasonPlayerStatistics()
            ->select([
                'seasons.year',
                'season_player_statistics.*',
            ])
            ->with([
                'competition',
                'team',
            ])
            ->leftJoin('seasons', 'season_player_statistics.season_id', '=', 'seasons.season_id')
            ->get()
            ->groupBy('year');

        return $this->jsonResponse($seasonPlayerStatistics, Response::HTTP_OK);
    }
}
