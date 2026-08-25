<?php

namespace App\Http\Controllers\Football\Matches;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvokeCompetitionRequest;
use App\Models\Competition;
use App\Support\MatchStreamPresenter;
use App\Support\MatchTimeQuery;
use App\Support\ScrapingCountryScope;
use App\Support\ViewerTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FootballMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(InvokeCompetitionRequest $request): JsonResponse
    {
        [$startDate, $endDate] = ViewerTimeZone::dayWindow(
            $request->filled('date') ? $request->date : null,
            ViewerTimeZone::fromRequest($request),
        );

        $competitionsMatches = Competition::query()
            ->select(['competition_id', 'country_id', 'category_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'short_name', 'cur_round', 'logo'])
            ->with([
                'category:category_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
                'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->tap(fn (Builder $q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
            ->withWhereHas('matches', function ($query) use ($startDate, $endDate, $request) {
                $query->select([
                    'match_id',
                    'competition_id',
                    'match_time',
                    'kickoff_time',
                    'home_team_id',
                    'away_team_id',
                    'status_id',
                    'home_scores',
                    'away_scores',
                    'mlive',
                ])
                    ->with([
                        'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                        'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                        'stream:match_id,playback_url,sport_id,match_time,synced_at,pushurl1,pushurl2',
                        'matchOdd' => fn ($query) => $query->where('company_id', '=', 2)->where('type', '=', $request->odd_type),
                    ])
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
                    ->when($request->is_finished, fn (Builder $query) => $query->where('status_id', '=', MatchStateEnum::END->value))
                    ->when($request->is_scheduled, fn (Builder $query) => $query->where('status_id', '=', MatchStateEnum::NOT_STARTED->value))
                    ->when($request->boolean('has_stream'), function (Builder $query) {
                        $query->whereHas('stream', fn (Builder $q) => $q->whereNotNull('playback_url')->where('playback_url', '!=', ''));
                    })
                    ->tap(fn (Builder $query) => MatchTimeQuery::applyBetween($query, $startDate, $endDate, true));
            })
            ->orderedBySequence()
            ->get();

        $competitionsMatches->each(function (Competition $competition) {
            $competition->matches->each(fn ($match) => MatchStreamPresenter::attach($match));
        });

        return $this->jsonResponse([
            'competition_matches' => $competitionsMatches,
        ], SymfonyResponse::HTTP_OK);
    }
}
