<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\DataScrapping\SingleMatchLineupDataScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MatchLineUpController extends Controller
{
    /** Vendor lineups appear about an hour before kick-off. */
    private const FETCH_FROM_KICKOFF_MINUTES = 90;

    private const FETCH_THROTTLE_MINUTES = 5;

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $matchLineUpData = $this->lineups($footballMatch);

        if ($matchLineUpData->isEmpty() && $this->mayFetchOnDemand($footballMatch)) {
            $fetched = false;
            try {
                $result = (new SingleMatchLineupDataScrapingService())
                    ->scrapRealtimeMatchData(['uuid' => $footballMatch->match_id]);
                $fetched = is_array($result) && ! isset($result['error']);
            } catch (\Throwable $exception) {
                info('lineups on-demand fetch failed: '.$exception->getMessage());
            }
            // Cache a successful call only. A failed call must be allowed to retry
            // on the next page view, otherwise every match stays without a lineup.
            if ($fetched) {
                Cache::put(
                    'lineups:on-demand:'.$footballMatch->match_id,
                    true,
                    now()->addMinutes(self::FETCH_THROTTLE_MINUTES),
                );
            }
            $matchLineUpData = $this->lineups($footballMatch);
        }

        $isStarting = static function ($row): bool {
            $v = $row->first;

            return $v === true || $v === 1 || $v === '1';
        };

        $starters = $matchLineUpData->filter($isStarting)->values();
        // Coordinates mean the player was placed in the XI even when `first` was stored wrong.
        if ($starters->isEmpty()) {
            $starters = $matchLineUpData
                ->filter(static fn ($row) => (int) $row->x > 0 || (int) $row->y > 0)
                ->values();
        }
        $starterIds = $starters->pluck('player_id');

        $data = [
            'match_lineups' => $starters,
            'on_the_bench' => $matchLineUpData
                ->reject(static fn ($row) => $starterIds->contains($row->player_id))
                ->values(),
            'match_injuries' => $footballMatch->matchInjuries()->get(),
        ];

        return $this->jsonResponse(collect($data), Response::HTTP_OK);
    }

    private function lineups(FootballMatch $footballMatch)
    {
        try {
            return $footballMatch->matchLineups()
                ->with(['player:id,player_id,name,short_name,logo'])
                ->get();
        } catch (\Throwable $exception) {
            info('lineups query failed: '.$exception->getMessage());

            return $footballMatch->matchLineups()->get();
        }
    }

    /**
     * The scheduled scraper only covers a window around kick-off, so a match it missed
     * is fetched when someone opens it. The throttle is recorded only after a
     * successful vendor response.
     */
    private function mayFetchOnDemand(FootballMatch $footballMatch): bool
    {
        $kickoff = (int) $footballMatch->match_time;
        if ($kickoff <= 0 || $kickoff > now()->addMinutes(self::FETCH_FROM_KICKOFF_MINUTES)->timestamp) {
            return false;
        }

        return ! Cache::has('lineups:on-demand:'.$footballMatch->match_id);
    }
}
