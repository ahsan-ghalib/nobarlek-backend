<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerTransferController extends Controller
{
    /**
     * Display a listing of the player transfers.
     */
    public function __invoke(Request $request, Player $player)
    {
        $playerTransfers = $player->playerTransfers()
            ->with([
                'fromTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'toTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->orderByDesc('transfer_time')
            ->get();

        return $this->jsonResponse($playerTransfers, Response::HTTP_OK);
    }
}
