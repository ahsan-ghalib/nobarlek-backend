<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Player;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerStatisticsCompetitionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        $competitions = Competition::query()
            ->select(['competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de'])
            ->tap(fn ($q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
            ->whereHas('seasons.seasonPlayerStatistics', fn (Builder $query) => $query->where('player_id', '=', $player->player_id))
            ->with(['seasons' => fn ($query) => $query->select(['competition_id', 'season_id', 'year'])->latest('year')])
            ->orderedBySequence()
            ->get();

        return $this->jsonResponse($competitions, Response::HTTP_OK);
    }
}
