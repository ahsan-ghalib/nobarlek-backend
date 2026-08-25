<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerSalaryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        $playerSalaries = $player->PlayerSalaries()
            ->with(['team:team_id,name,competition_id' => [
                'competition:competition_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            ]])
            ->latest('season')
            ->get();

        return $this->jsonResponse($playerSalaries, Response::HTTP_OK);
    }
}
