<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Repositories\FootballMatchRepository;
use App\Support\H2hMatchScorePresenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeadToHeadController extends Controller
{
    public FootballMatchRepository $footballMatchRepository;

    public function __construct()
    {
        $this->footballMatchRepository = (new FootballMatchRepository());
    }

    /**
     * Handle the incoming request.
     */
    public function homeH2h(Request $request, FootballMatch $footballMatch): JsonResponse
    {
        $homeHeadToHeadRows = $request->get('home_head_to_head_rows', 24);

        $homeHeadToHead = $this->footballMatchRepository->getSingleTeamMatchesOutcomes($footballMatch->home_team_id)
            ->whereIn('football_matches.home_team_id', [$footballMatch->home_team_id, $footballMatch->away_team_id])
            ->WhereIn('football_matches.away_team_id', [$footballMatch->home_team_id, $footballMatch->away_team_id])
            ->limit($homeHeadToHeadRows)
            ->get()
            ->map(fn ($match) => H2hMatchScorePresenter::enrich($match));

        return $this->jsonResponse([
            'matches' => $homeHeadToHead->groupBy('competition_name'),
            'counts' => $this->getMatchOutcomeCounts($homeHeadToHead),
        ], Response::HTTP_OK);
    }

    public function home(Request $request, FootballMatch $footballMatch): JsonResponse
    {
        $homeTeamRows = $request->get('home_team_rows', 6);

        //        $homeTeamFutureMatches = FootballMatch::query()
        //            ->where(fn($query) => $query->where('home_team_id', '=', $footballMatch->home_team_id)
        //                ->orWhere('away_team_id', '=', $footballMatch->home_team_id))
        //            ->where('football_matches.match_time', '>', now()->timestamp)
        //            ->select(['match_id'])
        //            ->limit(2)
        //            ->pluck('match_id')
        //            ->toArray();

        $homeTeamMatches = $this->footballMatchRepository->getSingleTeamMatchesOutcomes($footballMatch->home_team_id)
            ->where(fn ($query) => $query->where('football_matches.home_team_id', '=', $footballMatch->home_team_id)
                ->orWhere('football_matches.away_team_id', '=', $footballMatch->home_team_id)
            )
            ->where(fn ($query) => $query->where('football_matches.match_time', '<', now()->endOfMonth()->timestamp)
                // ->orWhereIn('football_matches.match_id', $homeTeamFutureMatches)
            )
//            ->leftJoin('seasons', 'competitions.cur_season_id', '=', 'seasons.season_id')
//            ->where('seasons.is_current', true)
            ->limit($homeTeamRows)
            ->get()
            ->map(fn ($match) => H2hMatchScorePresenter::enrich($match));

        return $this->jsonResponse([
            'matches' => $homeTeamMatches->groupBy('competition_name'),
            'counts' => $this->getMatchOutcomeCounts($homeTeamMatches),
        ], Response::HTTP_OK);
    }

    public function away(Request $request, FootballMatch $footballMatch): JsonResponse
    {
        $awayTeamRows = $request->get('away_team_rows', 6);
        //        $awayTeamFutureMatches = FootballMatch::query()
        //            ->where(fn($query) => $query->where('home_team_id', '=', $footballMatch->away_team_id)
        //                ->orWhere('away_team_id', '=', $footballMatch->away_team_id))
        //            ->where('football_matches.match_time', '>', now()->timestamp)
        //            ->select(['match_id'])
        //            ->limit(2)
        //            ->pluck('match_id')
        //            ->toArray();

        $awayTeamMatches = $this->footballMatchRepository->getSingleTeamMatchesOutcomes($footballMatch->away_team_id)
            ->where(fn ($query) => $query->where('football_matches.home_team_id', '=', $footballMatch->away_team_id)
                ->orWhere('football_matches.away_team_id', '=', $footballMatch->away_team_id))
            ->where(fn ($query) => $query->where('football_matches.match_time', '<', now()->endOfMonth()->timestamp)
                // ->orWhereIn('football_matches.match_id', $awayTeamFutureMatches)
            )
//            ->leftJoin('seasons', 'competitions.cur_season_id', '=', 'seasons.season_id')
//            ->where('seasons.is_current', true)
            ->limit($awayTeamRows)
            ->get()
            ->map(fn ($match) => H2hMatchScorePresenter::enrich($match));

        return $this->jsonResponse([
            'matches' => $awayTeamMatches->groupBy('competition_name'),
            'counts' => $this->getMatchOutcomeCounts($awayTeamMatches),
        ], Response::HTTP_OK);
    }

    /**
     * Default H2H listing: mutual matches between home and away (same payload as /home-h2h).
     */
    public function __invoke(Request $request, FootballMatch $footballMatch): JsonResponse
    {
        return $this->homeH2h($request, $footballMatch);
    }

    public function getMatchOutcomeCounts(Collection $matches): array
    {
        $matchOutcomes = array_fill_keys(['Win', 'Loss', 'Draw'], 0);

        return collect($matchOutcomes)->merge($matches->where('match_outcome', '!=', '-')->groupBy('match_outcome')->map->count())->toArray();
    }
}
