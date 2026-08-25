<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\TeamInjury;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerInjuriesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        $injuries = TeamInjury::query()
            ->where('player_id', '=', $player->player_id)
            ->with([
                'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->orderByDesc('start_time')
            ->get();

        return $this->jsonResponse($injuries, Response::HTTP_OK);
    }
}
