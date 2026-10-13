<?php

namespace App\Http\Controllers;

use App\Enums\MatchStateEnum;
use App\Models\Competition;
use App\Models\Country;
use App\Models\FootballMatch;
use App\Support\MatchStreamPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Homepage "Liga Indonesia" block: Indonesian matches in play, the next fixtures and
 * the latest results. Indonesian rounds are mostly played on weekends, so a
 * today-only list would be empty most days.
 */
class HomeIndonesiaMatchesController extends Controller
{
    private const UPCOMING_LIMIT = 8;

    private const RESULTS_LIMIT = 8;

    public function __invoke()
    {
        $data = Cache::remember('home:indonesia-matches', 20, function () {
            $competitions = $this->competitions();
            $ids = $competitions->pluck('competition_id')->all();

            if ($ids === []) {
                return ['competitions' => [], 'live' => [], 'upcoming' => [], 'results' => []];
            }

            $live = $this->matches($ids)
                ->whereIn('status_id', $this->liveStatuses())
                ->where('match_time', '>=', now()->subHours(4)->timestamp)
                ->orderBy('match_time')
                ->get();

            $upcoming = $this->matches($ids)
                ->where('status_id', MatchStateEnum::NOT_STARTED->value)
                ->where('match_time', '>=', now()->subMinutes(30)->timestamp)
                ->orderBy('match_time')
                ->limit(self::UPCOMING_LIMIT)
                ->get();

            $results = $this->matches($ids)
                ->where('status_id', MatchStateEnum::END->value)
                ->where('match_time', '>=', now()->subDays(14)->timestamp)
                ->orderByDesc('match_time')
                ->limit(self::RESULTS_LIMIT)
                ->get();

            foreach ([$live, $upcoming, $results] as $list) {
                $list->each(fn (FootballMatch $match) => MatchStreamPresenter::attach($match));
            }

            // Competitions that actually appear in the lists, top flight first.
            $used = $live->pluck('competition_id')
                ->merge($upcoming->pluck('competition_id'))
                ->merge($results->pluck('competition_id'))
                ->unique();

            return [
                'competitions' => $competitions->whereIn('competition_id', $used)->values(),
                'live' => $live->values(),
                'upcoming' => $upcoming->values(),
                'results' => $results->values(),
            ];
        });

        return $this->jsonResponse($data, Response::HTTP_OK);
    }

    private function competitions()
    {
        $countryId = Cache::remember(
            'home:indonesia-country-id',
            86400,
            fn () => Country::query()->where('name', 'Indonesia')->value('country_id'),
        );

        if (! $countryId) {
            return collect();
        }

        return Competition::query()
            ->where('country_id', $countryId)
            ->orderedBySequence()
            ->get(['competition_id', 'name', 'short_name', 'logo', 'priority']);
    }

    private function matches(array $competitionIds): Builder
    {
        return FootballMatch::query()
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
                'competition:competition_id,name,short_name,logo',
                'homeTeam:team_id,short_name,name,logo',
                'awayTeam:team_id,short_name,name,logo',
                'stream:match_id,sport_id,match_time,synced_at',
            ])
            ->whereIn('competition_id', $competitionIds);
    }

    private function liveStatuses(): array
    {
        return [
            MatchStateEnum::FIRST_HALF->value,
            MatchStateEnum::HALF_TIME->value,
            MatchStateEnum::SECOND_HALF->value,
            MatchStateEnum::OVERTIME->value,
            MatchStateEnum::OVERTIME_DEPRECATED->value,
            MatchStateEnum::PENALTY_SHOOT_OUT->value,
        ];
    }
}
