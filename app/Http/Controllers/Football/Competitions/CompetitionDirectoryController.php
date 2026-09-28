<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every competition that has matches, with counts the competitions page uses for filters and badges.
 */
class CompetitionDirectoryController extends Controller
{
    private const TYPES = [1 => 'league', 2 => 'cup', 3 => 'friendly'];

    private const FILTERS = ['all', 'league', 'cup', 'live', 'today'];

    private const SORTS = ['featured', 'az', 'busy'];

    private const MAX_PER_PAGE = 96;

    /**
     * Paginated directory. Query: page, per_page, q (name/country search),
     * filter (all|league|cup|live|today), sort (featured|az|busy).
     * `summary` and `featured` describe the whole directory, not just this page.
     */
    public function __invoke(Request $request)
    {
        // Live/today counts over every competition are costly; a minute of staleness is fine here.
        $key = 'competition-directory:'.(ScrapingCountryScope::shouldApply() ? 'scoped' : 'all');
        $all = collect(Cache::remember($key, 60, fn () => $this->directory()));

        // Clients from before pagination send no `page` and expect the plain list;
        // keep serving it so frontend and backend can be deployed in either order.
        if (! $request->has('page')) {
            return $this->jsonResponse($all->values(), Response::HTTP_OK);
        }

        $filter = in_array($request->query('filter'), self::FILTERS, true) ? $request->query('filter') : 'all';
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'featured';
        $perPage = max(1, min(self::MAX_PER_PAGE, (int) $request->query('per_page', 48)));
        $page = max(1, (int) $request->query('page', 1));
        $q = $this->normalize((string) $request->query('q', ''));

        $list = $all->filter(fn (array $c) => $this->matchesFilter($c, $filter));
        if ($q !== '') {
            $list = $list->filter(fn (array $c) => collect([$c['name'] ?? null, $c['short_name'] ?? null, $c['country']['name'] ?? null, $c['category']['name'] ?? null])
                ->filter()
                ->contains(fn ($v) => str_contains($this->normalize((string) $v), $q)));
        }
        $list = match ($sort) {
            'az' => $list->sortBy(fn (array $c) => $this->normalize((string) ($c['short_name'] ?: $c['name'])))->values(),
            'busy' => $list->sortByDesc(fn (array $c) => $c['live_count'] * 100 + $c['today_count'] * 10 + $c['upcoming_count'])->values(),
            default => $list->values(), // cached order: top competitions first, then priority
        };

        $total = $list->count();

        return $this->jsonResponse([
            'data' => $list->forPage($page, $perPage)->values(),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
            'summary' => [
                'total' => $all->count(),
                'live' => $all->sum('live_count'),
                'today' => $all->sum('today_count'),
                'filters' => collect(self::FILTERS)
                    ->mapWithKeys(fn (string $f) => [$f => $all->filter(fn (array $c) => $this->matchesFilter($c, $f))->count()]),
            ],
            'featured' => $all->where('is_top', true)->take(4)->values(),
        ], Response::HTTP_OK);
    }

    private function matchesFilter(array $c, string $filter): bool
    {
        return match ($filter) {
            'league' => $c['type'] === 'league',
            'cup' => $c['type'] === 'cup',
            'live' => $c['live_count'] > 0,
            'today' => $c['today_count'] > 0,
            default => true,
        };
    }

    private function normalize(string $value): string
    {
        return trim(Str::lower(Str::ascii($value)));
    }

    private function directory(): array
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

        return Competition::query()
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
            })
            ->all();
    }
}
