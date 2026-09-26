<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every competition that has matches, with counts the competitions page uses for filters and badges.
 */
class CompetitionDirectoryController extends Controller
{
    private const TYPES = [1 => 'league', 2 => 'cup', 3 => 'friendly'];

    public function __invoke()
    {
        $todayStart = now()->startOfDay()->timestamp;
        $todayEnd = now()->endOfDay()->timestamp;
        $weekEnd = now()->addDays(7)->endOfDay()->timestamp;
        $liveStatuses = [
            MatchStateEnum::FIRST_HALF->value,
            MatchStateEnum::SECOND_HALF->value,
            MatchStateEnum::HALF_TIME->value,
            MatchStateEnum::OVERTIME->value,
            MatchStateEnum::OVERTIME_DEPRECATED->value,
            MatchStateEnum::PENALTY_SHOOT_OUT->value,
        ];

        $competitions = Competition::query()
            ->select(['competition_id', 'country_id', 'category_id', 'name', 'short_name', 'logo', 'type', 'top_order'])
            ->with([
                'country:country_id,name,logo',
                'category:category_id,name',
            ])
            ->tap(fn (Builder $q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
            // Admin-marked top competitions always appear, even before they have matches.
            ->where(fn (Builder $q) => $q->whereHas('matches')->orWhereNotNull('top_order'))
            ->withCount([
                // Time-bounded: stale rows can keep a live status long after the match ended.
                'matches as live_count' => fn (Builder $q) => $q
                    ->whereIn('status_id', $liveStatuses)
                    ->whereBetween('match_time', [now()->subHours(4)->timestamp, now()->timestamp]),
                'matches as today_count' => fn (Builder $q) => $q->whereBetween('match_time', [$todayStart, $todayEnd]),
                'matches as upcoming_count' => fn (Builder $q) => $q
                    ->where('status_id', MatchStateEnum::NOT_STARTED->value)
                    ->whereBetween('match_time', [now()->timestamp, $weekEnd]),
            ])
            ->orderByRaw('CASE WHEN top_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('top_order')
            ->orderedBySequence()
            ->get()
            ->map(function (Competition $competition) {
                $data = $competition->toArray();
                $data['type'] = self::TYPES[(int) $competition->getRawOriginal('type')] ?? 'other';
                $data['is_top'] = $competition->top_order !== null;

                return $data;
            });

        return $this->jsonResponse($competitions, Response::HTTP_OK);
    }
}
