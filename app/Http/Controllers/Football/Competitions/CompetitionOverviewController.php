<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use App\Models\Stage;
use App\Support\SeasonPlayerLeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition, Season $season)
    {
        $seasonId = $season->season_id ?? $competition->cur_season_id;
        $topScorers = SeasonPlayerLeaders::topScorers($seasonId);
        $topAssists = SeasonPlayerLeaders::topAssists($seasonId);

        $stages = Stage::query()
            ->when(isset($season->season_id), fn ($query) => $query->where('season_id', '=', $season->season_id),
                fn ($query) => $query->where('season_id', '=', $competition->cur_season_id)
            )
            ->orderBy('order')
            ->get();

        $competition->load([
            'category:category_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            'stage:stage_id,name,mode,group_count,round_count,order',
            'currentSeason',
        ]);

        $competition->append(['meta_keywords']);

        $competition->current_stages = $stages;

        $data = [
            'overview' => $competition,
            'top_scorer' => $topScorers,
            'top_assists' => $topAssists,
            'prem_stats' => [
                'number_of_players' => $competition->loadSum('teams', 'total_players')->teams_sum_total_players,
                'foreign_players' => $competition->loadSum('teams', 'foreign_players')->teams_sum_foreign_players,
                'number_of_teams' => $competition->loadCount('teams')->teams_count,
                'total_market_value' => $competition->loadSum('teams', 'market_value')->teams_sum_market_value,
                'total_market_value_currency' => '€',
            ],
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
