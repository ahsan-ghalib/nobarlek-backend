<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamSalaryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        $teamSalaries = $team->squads()
            ->with([
                'player:id,player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,age,contract_until,country_id,market_value,market_value_currency' => [
                    'country:country_id,name,logo',
                    'PlayerSalary',
                ],
            ])
            ->whereNotNull('position')
            ->get()
            ->groupBy('position');

        $data = [
            'salaries' => $teamSalaries,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
