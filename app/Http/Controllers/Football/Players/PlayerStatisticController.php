<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonPlayerStatistics;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerStatisticController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player, Season $season)
    {
        $seasonPlayerStatistics = SeasonPlayerStatistics::query()
            ->where('season_id', '=', $season->season_id)
            ->where('player_id', '=', $player->player_id)
            ->first();

        return $this->jsonResponse(collect($seasonPlayerStatistics), Response::HTTP_OK);
    }
}
