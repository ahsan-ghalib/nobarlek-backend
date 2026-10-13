<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Left-menu competitions: Indonesian leagues first, then the top leagues (the admin's
 * pick, or a default set), then the main European club tournaments. Everything else
 * is served separately as "all leagues" so every page doesn't ship (and render)
 * thousands of competitions.
 */
class SidebarCompetitionController extends Controller
{
    private const COLUMNS = ['competition_id', 'country_id', 'name', 'short_name', 'logo', 'top_order', 'priority'];

    /** European club tournaments shown in the menu, in this order; other UEFA competitions go under "all leagues". */
    private const EUROPEAN_ORDER = [
        'UEFA Champions League',
        'UEFA Europa League',
        'UEFA Europa Conference League',
        'UEFA Conference League',
        'UEFA Super Cup',
    ];

    /** Used when the admin has not picked any (non-Indonesian) top competitions. */
    private const DEFAULT_TOP = [
        'English Premier League',
        'Spanish La Liga',
        'Italian Serie A',
        'Bundesliga',
        'French Ligue 1',
        'Saudi Professional League',
        'AFC Champions League Elite',
        'FIFA World Cup qualification (AFC)',
    ];

    private const INDONESIA_LIMIT = 8;

    private const CACHE_SECONDS = 300;

    public function index(): JsonResponse
    {
        $data = Cache::remember($this->cacheKey('index'), self::CACHE_SECONDS, function () {
            $indonesia = $this->indonesiaCompetitions();

            $top = $this->topCompetitions();

            $european = $this->europeanQuery()
                ->whereNotIn('competition_id', $top->pluck('competition_id'))
                ->get(self::COLUMNS)
                ->sortBy(fn (Competition $c) => [$this->europeanRank($c), $c->priority ?? PHP_INT_MAX, $c->name])
                ->values();

            $shown = $indonesia->pluck('competition_id')
                ->merge($top->pluck('competition_id'))
                ->merge($european->pluck('competition_id'));

            return [
                'indonesia' => $this->present($indonesia),
                'top' => $this->present($top),
                'european' => $this->present($european),
                'other_count' => $this->othersQuery($shown)->count(),
            ];
        });

        return $this->jsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Remaining competitions that have matches, grouped by country (international
     * competitions without a country are grouped by region).
     */
    public function others(): JsonResponse
    {
        $data = Cache::remember($this->cacheKey('others'), self::CACHE_SECONDS, function () {
            $top = $this->topCompetitions();
            $shown = $this->indonesiaCompetitions()->pluck('competition_id')
                ->merge($top->pluck('competition_id'))
                ->merge($this->europeanQuery()->pluck('competition_id'));

            return $this->othersQuery($shown)
                ->with(['country:country_id,name,logo', 'category:category_id,name'])
                ->orderedBySequence()
                ->get([...self::COLUMNS, 'category_id'])
                ->groupBy(fn (Competition $c) => $c->country?->name ?: ($c->category?->name ?: 'International'))
                ->map(fn (Collection $items, string $group) => [
                    'name' => $group,
                    'logo' => $items->first()->country?->logo,
                    'competitions' => $this->present($items),
                ])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        });

        return $this->jsonResponse($data, Response::HTTP_OK);
    }

    private function baseQuery(): Builder
    {
        return Competition::query()->tap(fn (Builder $q) => ScrapingCountryScope::restrictCompetitionsQuery($q));
    }

    /** Admin-picked top competitions without the Indonesian ones (they have their own group). */
    private function topCompetitions(): Collection
    {
        $notIndonesian = fn (Builder $q) => $q->where(fn (Builder $qq) => $qq
            ->whereNull('country_id')
            ->orWhere('country_id', '!=', $this->indonesiaCountryId() ?? ''));

        $top = $this->baseQuery()
            ->whereNotNull('top_order')
            ->tap($notIndonesian)
            ->orderBy('top_order')
            ->get(self::COLUMNS);

        if ($top->isNotEmpty()) {
            return $top;
        }

        return $this->baseQuery()
            ->whereIn('name', self::DEFAULT_TOP)
            ->get(self::COLUMNS)
            ->sortBy(fn (Competition $c) => array_search($c->name, self::DEFAULT_TOP, true))
            ->values();
    }

    /** Admin-picked top competitions first, then the busiest around now (leagues before one-off local cups). */
    private function indonesiaCompetitions(): Collection
    {
        $window = [now()->subDays(30)->timestamp, now()->addDays(30)->timestamp];

        return $this->indonesiaQuery()
            ->whereHas('matches')
            ->withCount(['matches as recent_matches' => fn (Builder $q) => $q->whereBetween('match_time', $window)])
            ->orderByRaw('CASE WHEN top_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('top_order')
            ->orderByDesc('recent_matches')
            ->orderBy('name')
            ->limit(self::INDONESIA_LIMIT)
            ->get(self::COLUMNS);
    }

    private function indonesiaQuery(): Builder
    {
        $countryId = $this->indonesiaCountryId();

        return $countryId
            ? $this->baseQuery()->where('country_id', $countryId)
            : $this->baseQuery()->whereRaw('0 = 1');
    }

    private function indonesiaCountryId(): ?string
    {
        return Cache::remember(
            'sidebar-competitions:indonesia-country-id',
            86400,
            fn () => Country::query()->where('name', 'Indonesia')->value('country_id'),
        );
    }

    private function europeanQuery(): Builder
    {
        return $this->baseQuery()->whereIn('name', self::EUROPEAN_ORDER);
    }

    /**
     * @param  Collection<int, string>  $exclude
     */
    private function othersQuery(Collection $exclude): Builder
    {
        return $this->baseQuery()
            ->whereNotIn('competition_id', $exclude->all())
            ->whereHas('matches');
    }

    private function europeanRank(Competition $competition): int
    {
        $index = array_search($competition->name, self::EUROPEAN_ORDER, true);

        return $index === false ? count(self::EUROPEAN_ORDER) : $index;
    }

    private function present(Collection $competitions): Collection
    {
        return $competitions->map(fn (Competition $c) => [
            'competition_id' => $c->competition_id,
            'name' => $c->name,
            'short_name' => $c->short_name,
            'logo' => $c->logo,
        ])->values();
    }

    /** Called when the admin changes the top competitions, so the menu updates right away. */
    public static function forgetCache(): void
    {
        foreach (['index', 'others'] as $part) {
            foreach (['scoped', 'all'] as $scope) {
                Cache::forget("sidebar-competitions:{$part}:{$scope}");
            }
        }
    }

    private function cacheKey(string $part): string
    {
        return 'sidebar-competitions:'.$part.':'.(ScrapingCountryScope::shouldApply() ? 'scoped' : 'all');
    }
}
