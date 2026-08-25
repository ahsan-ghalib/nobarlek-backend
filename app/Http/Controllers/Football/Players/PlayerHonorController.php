<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerHonorController extends Controller
{
    /**
     * Display a listing of the player honors.
     */
    public function __invoke(Request $request, Player $player)
    {
        $honors = $player->playerHonors()
            ->select([
                'honors.name as honor_name',
                'honors.logo as honor_logo',
                'seasons.year as season_year',
                'player_honors.season as season',
                'competitions.name as competition_name',
            ])
            ->leftJoin('honors', 'player_honors.honor_id', '=', 'honors.honor_id')
            ->leftJoin('competitions', 'player_honors.competition_id', '=', 'competitions.competition_id')
            ->leftJoin('seasons', 'player_honors.season_id', '=', 'seasons.season_id')
            // ->whereNotNull('competitions.name')
            ->get()
            ->groupBy('competition_name');

        return $this->jsonResponse($honors, Response::HTTP_OK);
    }
}
