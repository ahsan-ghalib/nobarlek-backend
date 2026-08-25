<?php

namespace App\Http\Controllers\Football\Teams;

use App\Enums\PlayerTransferTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\PlayerTransfer;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamTransferController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team, ?int $year = null)
    {
        $teamTransfers = PlayerTransfer::query()
            ->with([
                'player:player_id,name,logo,age,position,country_id' => [
                    'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
                ],
                'fromTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'toTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->when($year !== null, function (Builder $query) use ($year) {
                $query->whereBetween('transfer_time', [
                    Carbon::createFromDate($year, 1, 1)->startOfYear()->timestamp,
                    Carbon::createFromDate($year, 1, 1)->endOfYear()->timestamp,
                ]);
            })
            ->when(
                isset($request->loan) && $request->loan === 'yes',
                fn (Builder $query) => $query->where('transfer_type', '=', array_search(PlayerTransferTypeEnum::LOAN->value, PlayerTransferTypeEnum::values(), true))
            )
            ->when(
                isset($request->transfer) && $request->transfer === 'yes',
                fn (Builder $query) => $query->where('transfer_type', '=', array_search(PlayerTransferTypeEnum::TRANSFER->value, PlayerTransferTypeEnum::values(), true))
            )
            ->where(fn (Builder $query) => $query->where('from_team_id', '=', $team->team_id)
                ->orWhere('to_team_id', '=', $team->team_id))
            ->orderByDesc('transfer_time')
            ->get();

        $topValueAblePlayer = $team->players()
            ->select([
                'name', 'logo', 'market_value', 'market_value_currency', 'position',
            ])
            ->orderByDesc('market_value')
            ->limit(10)
            ->get();

        $data = [
            'team_transfers' => $teamTransfers,
            'top_valueable_player' => $topValueAblePlayer,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
