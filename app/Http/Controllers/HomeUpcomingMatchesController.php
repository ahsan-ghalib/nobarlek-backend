<?php

namespace App\Http\Controllers;

use App\Enums\MatchStateEnum;
use App\Models\FootballMatch;
use App\Support\MatchStreamPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Homepage "upcoming fixtures" block for the top competitions. The frontend owns the
 * list of top competitions and passes it as `competitions=id,id,...`.
 */
class HomeUpcomingMatchesController extends Controller
{
    private const DAYS_AHEAD = 21;

    private const PER_COMPETITION = 2;

    private const MAX_COMPETITIONS = 24;

    public function __invoke(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('competitions', '')))
            ->map(fn (string $id) => trim($id))
            ->filter(fn (string $id) => preg_match('/^[a-z0-9]{6,32}$/i', $id) === 1)
            ->unique()
            ->take(self::MAX_COMPETITIONS)
            ->values()
            ->all();

        if ($ids === []) {
            return $this->jsonResponse([], Response::HTTP_OK);
        }

        $cacheKey = 'home:upcoming-matches:v2:'.md5(implode(',', $ids));
        $data = Cache::remember($cacheKey, 60, function () use ($ids) {
            $matches = FootballMatch::query()
                ->select([
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
                    'competition:competition_id,name,short_name,logo,country_id',
                    'competition.country:country_id,name',
                    'homeTeam:team_id,short_name,name,logo',
                    'awayTeam:team_id,short_name,name,logo',
                    'stream:match_id,sport_id,match_time,synced_at',
                ])
                ->whereIn('competition_id', $ids)
                ->where('status_id', MatchStateEnum::NOT_STARTED->value)
                ->whereBetween('match_time', [
                    now()->subMinutes(30)->timestamp,
                    now()->addDays(self::DAYS_AHEAD)->timestamp,
                ])
                ->orderBy('match_time')
                ->get();

            // Keep the caller's competition order (the sidebar's top leagues) and
            // the soonest fixtures inside each one. Sorting every match by kick-off
            // let whichever league played first crowd the others out.
            $byCompetition = $matches->groupBy('competition_id');
            $upcoming = collect($ids)
                ->flatMap(function (string $id) use ($byCompetition) {
                    $group = $byCompetition->get($id);

                    return $group ? $group->sortBy('match_time')->take(self::PER_COMPETITION) : [];
                })
                ->values();

            $upcoming->each(fn (FootballMatch $match) => MatchStreamPresenter::attach($match));

            return $upcoming;
        });

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
