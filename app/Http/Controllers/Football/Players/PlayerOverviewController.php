<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        $player->load([
            'team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'playerLatestSquad:team_squads.player_id,position,shirt_number',
            'playerTransfers' => function ($query) {
                $query
                    ->with([
                        'fromTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                        'toTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                    ])
                    ->orderByDesc('transfer_time');
            },
        ]);

        $player->append(['meta_keywords']);

        return $this->jsonResponse(collect($player), Response::HTTP_OK);
    }
}
