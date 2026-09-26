<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PopularCompetitionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Admin-curated list from the dashboard takes precedence over the defaults below.
        $top = Competition::query()
            ->select(['competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo'])
            ->whereNotNull('top_order')
            ->orderBy('top_order')
            ->get();

        if ($top->isNotEmpty()) {
            return $this->jsonResponse($top, Response::HTTP_OK);
        }

        $competitions = Competition::query()
            ->select(['competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo'])
            ->when(
                ScrapingCountryScope::shouldApply(),
                fn ($q) => ScrapingCountryScope::restrictCompetitionsQuery($q)->orderedBySequence(),
                fn ($q) => $q->whereIn('name', [
                    'UEFA Champions League',
                    'UEFA Europa League',
                    'UEFA Europa Conference League',
                    'English Premier League',
                    'Spanish La Liga',
                    'Bundesliga',
                    'Italian Serie A',
                    'French Ligue 1',
                    'Netherlands Eredivisie',
                    'Brazilian Serie A',
                ])->orderedBySequence()
            )
            ->get();

        // The default names may not exist in this dataset; fall back to competitions that have matches.
        if ($competitions->isEmpty()) {
            $competitions = Competition::query()
                ->select(['competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo'])
                ->whereHas('matches')
                ->withCount('matches')
                ->orderByDesc('matches_count')
                ->limit(10)
                ->get()
                ->makeHidden('matches_count');
        }

        return $this->jsonResponse($competitions, Response::HTTP_OK);
    }
}
