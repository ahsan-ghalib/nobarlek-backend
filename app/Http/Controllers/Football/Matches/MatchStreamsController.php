<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\MatchStream;
use App\Support\MatchStreamPresenter;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MatchStreamsController extends Controller
{
    /**
     * Matches that currently have a watchable stream (playback_url present).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $oddType = $request->input('odd_type', 'eu');

        $streamMatchIds = MatchStream::query()
            ->whereNotNull('playback_url')
            ->where('playback_url', '!=', '')
            ->pluck('match_id');

        if ($streamMatchIds->isEmpty()) {
            return $this->jsonResponse([
                'competition_matches' => [],
            ], SymfonyResponse::HTTP_OK);
        }

        $competitionsMatches = Competition::query()
            ->select(['competition_id', 'country_id', 'category_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'short_name', 'cur_round', 'logo'])
            ->with([
                'category:category_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
                'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->tap(fn (Builder $q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
            ->withWhereHas('matches', function ($query) use ($streamMatchIds, $oddType) {
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
                    ->whereIn('match_id', $streamMatchIds)
                    ->with([
                        'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                        'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                        'stream:match_id,playback_url,sport_id,match_time,synced_at,pushurl1,pushurl2',
                        'matchOdd' => fn ($q) => $q->where('company_id', '=', 2)->where('type', '=', $oddType),
                    ])
                    ->orderBy('match_time');
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
