<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\CompetitionSeasonController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Football\Matches\CompetitionFootballMatchController;
use App\Models\Competition;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionPageBootstrapController extends Controller
{
    public function __invoke(Request $request, Competition $competition): JsonResponse
    {
        $season = new Season;
        $standingsRequest = Request::create(
            $request->getUri(),
            'GET',
            ['type' => 'All']
        );

        $overview = $this->data(
            (new CompetitionOverviewController)($request, $competition, $season)
        );
        $standings = $this->data(
            (new CompetitionStandingController)->fullTeamStandings(
                $standingsRequest,
                $competition,
                $season
            )
        );
        $seasons = $this->data(
            (new CompetitionSeasonController)($request, $competition)
        );
        $results = $this->data(
            (new CompetitionSeasonResultsController)($request, $competition, $season)
        );
        $matches = $this->data(
            (new CompetitionFootballMatchController)($request, $competition)
        );
        $news = $this->data(
            (new CompetitionNewsController)($request, $competition)
        );

        return $this->jsonResponse([
            'overview' => $overview,
            'standings' => $standings,
            'seasons' => $seasons,
            'results' => $results,
            'matches' => $matches,
            'news' => $news,
        ], Response::HTTP_OK);
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    private function data(JsonResponse $response): array
    {
        $payload = $response->getData(true);

        return is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }
}
