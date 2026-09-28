<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Left-menu competitions: European club tournaments first, then the admin's top
 * leagues, then everything else. The long "all leagues" list is served separately
 * so every page doesn't ship (and render) thousands of competitions.
 */
class SidebarCompetitionController extends Controller
{
    private const COLUMNS = ['competition_id', 'country_id', 'name', 'short_name', 'logo', 'top_order', 'priority'];

    /** European competitions in the order the menu lists them; any other UEFA competition follows. */
    private const EUROPEAN_ORDER = [
        'UEFA Champions League',
        'UEFA Europa League',
        'UEFA Europa Conference League',
        'UEFA Conference League',
        'UEFA Super Cup',
    ];

    private const CACHE_SECONDS = 300;

    public function index(): JsonResponse
    {
        $data = Cache::remember($this->cacheKey('index'), self::CACHE_SECONDS, function () {
            $european = $this->europeanQuery()->get(self::COLUMNS)
                ->sortBy(fn (Competition $c) => [$this->europeanRank($c), $c->priority ?? PHP_INT_MAX, $c->name])
                ->values();

            $top = $this->baseQuery()
                ->whereNotNull('top_order')
                ->whereNotIn('competition_id', $european->pluck('competition_id'))
                ->orderBy('top_order')
                ->get(self::COLUMNS);

            $shown = $european->pluck('competition_id')->merge($top->pluck('competition_id'));

            return [
                'european' => $this->present($european),
                'top' => $this->present($top),
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
            $shown = $this->europeanQuery()->pluck('competition_id')
                ->merge($this->baseQuery()->whereNotNull('top_order')->pluck('competition_id'));

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

    private function europeanQuery(): Builder
    {
        return $this->baseQuery()->where(fn (Builder $q) => $q
            ->where('name', 'like', 'UEFA %')
            ->orWhereIn('short_name', ['UCL', 'UEL', 'UECL']));
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
