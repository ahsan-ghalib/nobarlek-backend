<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchLineUpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $matchLineUpData = $footballMatch->matchLineups()
            ->with([
                'player:id,player_id,name,short_name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,age',
            ])
            ->get();

        $isStarting = static function ($row): bool {
            $v = $row->first;

            return $v === true || $v === 1 || $v === '1';
        };

        $data = [
            'match_lineups' => $matchLineUpData->filter($isStarting)->values(),
            'on_the_bench' => $matchLineUpData->filter(static fn ($row) => ! $isStarting($row))->values(),
            'match_injuries' => $footballMatch->matchInjuries()->get(),
        ];

        return $this->jsonResponse(collect($data), Response::HTTP_OK);
    }
}
