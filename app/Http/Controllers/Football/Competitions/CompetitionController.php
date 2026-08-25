<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvokeCompetitionRequest;
use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(InvokeCompetitionRequest $request, ?string $date = null)
    {
        $competitions = Competition::query()
            ->select(['country_id', 'category_id', 'competition_id', 'cur_season_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo'])
            ->with([
                'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'category:category_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            ])
            ->tap(fn (Builder $q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
            ->whereHas('matches', fn (Builder $query) => $query->whereBetween('match_time', [now()->startOfDay()->timestamp, now()->endOfDay()->timestamp]))
            ->when($request->is_live, function (Builder $query) {
                $query->whereIn('status_id', [
                    MatchStateEnum::FIRST_HALF->value,
                    MatchStateEnum::SECOND_HALF->value,
                    MatchStateEnum::HALF_TIME->value,
                    MatchStateEnum::OVERTIME->value,
                    MatchStateEnum::OVERTIME_DEPRECATED->value,
                    MatchStateEnum::PENALTY_SHOOT_OUT->value,
                ]);
            })
            ->withCount([
                'matches' => fn (Builder $query) => $query->whereBetween('match_time', [now()->startOfDay()->timestamp, now()->endOfDay()->timestamp]),
                'matches as live_matches' => fn (Builder $query) => $query->whereBetween('match_time', [now()->startOfDay()->timestamp, now()->endOfDay()->timestamp]),
            ])
            ->orderedBySequence()
            ->get();

        $favoriteCompetitions = [];

        return $this->jsonResponse(collect([
            'favorite_leagues' => $favoriteCompetitions,
            'other_leagues' => $competitions,
        ]), Response::HTTP_OK);
    }
}
