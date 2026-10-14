<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\CompetitionSeasonController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Football\Matches\CompetitionFootballMatchController;
use App\Models\Competition;
use App\Models\Season;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            fn () => (new CompetitionOverviewController)($request, $competition, $season)
        );
        $standings = $this->data(
            fn () => (new CompetitionStandingController)->fullTeamStandings(
                $standingsRequest,
                $competition,
                $season
            )
        );
        $seasons = $this->data(
            fn () => (new CompetitionSeasonController)($request, $competition)
        );
        $results = $this->data(
            fn () => (new CompetitionSeasonResultsController)($request, $competition, $season)
        );
        $matches = $this->data(
            fn () => (new CompetitionFootballMatchController)($request, $competition)
        );
        $news = $this->data(
            fn () => (new CompetitionNewsController)($request, $competition)
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
     * Sections abort with 404 when the competition has no current season (cups,
     * friendlies); that must leave the section empty instead of failing the page.
     *
     * @param  Closure(): JsonResponse  $section
     * @return array<string, mixed>|array<int, mixed>
     */
    private function data(Closure $section): array
    {
        try {
            $payload = $section()->getData(true);
        } catch (HttpException) {
            return [];
        }

        return is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }
}
