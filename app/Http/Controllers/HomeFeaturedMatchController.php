<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\HomeFeaturedMatch;
use App\Support\MatchStreamPresenter;
use Symfony\Component\HttpFoundation\Response;

class HomeFeaturedMatchController extends Controller
{
    public function __invoke()
    {
        $featured = HomeFeaturedMatch::instance();

        if (!$featured->is_active || !$featured->match_id) {
            return $this->emptyResponse();
        }

        $tickerIds = $featured->tickerMatchIds();

        $matches = FootballMatch::query()
            ->select([
                'match_id',
                'competition_id',
                'venue_id',
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
                'competition:competition_id,name,short_name,logo',
                'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'venue:venue_id,name',
                'stream:match_id,sport_id,match_time,synced_at',
            ])
            ->whereIn('match_id', [$featured->match_id, ...$tickerIds])
            ->get()
            ->each(fn (FootballMatch $match) => MatchStreamPresenter::attach($match))
            ->keyBy('match_id');

        $match = $matches->get($featured->match_id);

        if (!$match) {
            return $this->emptyResponse();
        }

        return $this->jsonResponse([
            'banner_url' => $featured->banner_url,
            'match' => $match,
            'ticker' => collect($tickerIds)->map(fn ($id) => $matches->get($id))->filter()->values(),
        ], Response::HTTP_OK);
    }

    private function emptyResponse()
    {
        return response()->json([
            'message' => 'No featured match',
            'data' => null,
        ], Response::HTTP_OK);
    }
}
