<?php

namespace App\Http\Controllers\Dashboard\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateHomeFeaturedMatchRequest;
use App\Models\FootballMatch;
use App\Models\HomeFeaturedMatch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeFeaturedMatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:home.featured-match');
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->payload(HomeFeaturedMatch::instance()),
            'message' => 'Successfully retrieved featured match',
        ]);
    }

    public function update(UpdateHomeFeaturedMatchRequest $request): JsonResponse
    {
        $featured = HomeFeaturedMatch::instance();
        $validated = $request->validated();
        $oldBanner = $featured->banner;

        $data = [
            'match_id' => $validated['match_id'] ?? null,
            'ticker_match_ids' => $validated['ticker_match_ids'] ?? [],
            'is_active' => $validated['is_active'],
        ];

        if ($request->hasFile('banner')) {
            $data['banner'] = Storage::disk('public')->put('/featured-matches', $request->file('banner'));
        } elseif ($validated['remove_banner']) {
            $data['banner'] = null;
        }

        $featured->update($data);

        if (array_key_exists('banner', $data) && $oldBanner && $oldBanner !== $data['banner']) {
            Storage::disk('public')->delete($oldBanner);
        }

        return response()->json([
            'data' => $this->payload($featured->fresh()),
            'message' => 'Successfully featured match updated',
        ]);
    }

    /**
     * Match picker search. Without a query it lists matches around today; with a query it searches
     * all matches by team / competition name or match id. Matches closest to now come first.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $now = Carbon::now()->timestamp;

        $matches = $this->summaryQuery()
            ->when($q === '', fn ($query) => $query->whereBetween('match_time', [
                Carbon::now()->subDays(1)->timestamp,
                Carbon::now()->addDays(3)->timestamp,
            ]))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('match_id', 'like', "%{$q}%")
                        ->orWhereHas('competition', fn ($cq) => $cq->where('name', 'like', "%{$q}%")->orWhere('short_name', 'like', "%{$q}%"))
                        ->orWhereHas('homeTeam', fn ($tq) => $tq->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('awayTeam', fn ($tq) => $tq->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderByRaw('ABS(CAST(match_time AS SIGNED) - ?)', [$now])
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $matches,
            'message' => 'Successfully retrieved matches',
        ]);
    }

    private function payload(HomeFeaturedMatch $featured): array
    {
        $ids = array_filter([$featured->match_id, ...$featured->tickerMatchIds()]);
        /** @var Collection<string, FootballMatch> $matches */
        $matches = $this->summaryQuery()->whereIn('match_id', $ids)->get()->keyBy('match_id');

        return [
            'match_id' => $featured->match_id,
            'ticker_match_ids' => $featured->tickerMatchIds(),
            'is_active' => $featured->is_active,
            'banner_url' => $featured->banner_url,
            'match' => $featured->match_id ? $matches->get($featured->match_id) : null,
            'ticker' => collect($featured->tickerMatchIds())
                ->map(fn ($id) => $matches->get($id))
                ->filter()
                ->values(),
        ];
    }

    private function summaryQuery()
    {
        return FootballMatch::query()
            ->select(['id', 'match_id', 'competition_id', 'home_team_id', 'away_team_id', 'status_id', 'match_time', 'home_scores', 'away_scores'])
            ->with([
                'competition:competition_id,name,short_name',
                'homeTeam:team_id,name,logo',
                'awayTeam:team_id,name,logo',
            ]);
    }
}
