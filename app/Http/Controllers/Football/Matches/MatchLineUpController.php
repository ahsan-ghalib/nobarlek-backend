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

        if ($matchLineUpData->isEmpty() && $this->shouldFetchOnDemand($footballMatch)) {
            try {
                (new SingleMatchLineupDataScrapingService())
                    ->scrapRealtimeMatchData(['uuid' => $footballMatch->match_id]);
            } catch (\Throwable $exception) {
                info('lineups on-demand fetch failed: '.$exception->getMessage());
            }
            $matchLineUpData = $this->lineups($footballMatch);
        }

        $isStarting = static function ($row): bool {
            $v = $row->first;

            return $v === true || $v === 1 || $v === '1';
        };

        $data = [
            'match_lineups' => $matchLineUpData->filter($isStarting)->values(),
            'on_the_bench' => $matchLineUpData->filter(static fn ($row) => ! $isStarting($row))->values(),
            'match_injuries' => $footballMatch->matchInjuries()->get(),
        ];

        return $this->jsonResponse(collect($data), Response::HTTP_OK);
    }

    private function lineups(FootballMatch $footballMatch)
    {
        return $footballMatch->matchLineups()
            ->with([
                'player:id,player_id,name,short_name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,age',
            ])
            ->get();
    }

    /**
     * The scheduled scraper only covers a window around kick-off, so a match it missed
     * is fetched when someone opens it, at most once per throttle window.
     */
    private function shouldFetchOnDemand(FootballMatch $footballMatch): bool
    {
        $kickoff = (int) $footballMatch->match_time;
        if ($kickoff <= 0 || $kickoff > now()->addMinutes(self::FETCH_FROM_KICKOFF_MINUTES)->timestamp) {
            return false;
        }

        return Cache::add(
            'lineups:on-demand:'.$footballMatch->match_id,
            true,
            now()->addMinutes(self::FETCH_THROTTLE_MINUTES),
        );
    }
}
