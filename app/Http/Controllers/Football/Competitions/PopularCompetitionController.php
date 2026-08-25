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

        return $this->jsonResponse($competitions, Response::HTTP_OK);
    }
}
