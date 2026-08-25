<?php

namespace App\Http\Controllers;

use App\Enums\MatchStateEnum;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Models\Team;
use App\Support\ScrapingCountryScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FooterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (ScrapingCountryScope::shouldApply()) {
            $leagues = Competition::query()
                ->select([
                    'competition_id',
                    'name',
                    'name_aa',
                    'name_nl',
                    'name_vi',
                    'name_pt',
                    'name_br',
                    'name_es',
                    'name_fr',
                    'name_de',
                    'logo',
                ])
                ->tap(fn ($q) => ScrapingCountryScope::restrictCompetitionsQuery($q))
                ->orderedBySequence()
                ->get();

            $nextMatches = FootballMatch::query()
                ->select('match_id', 'home_team_id', 'away_team_id', 'status_id', 'kickoff_time')
                ->with(['homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr', 'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de'])
                ->tap(fn ($q) => ScrapingCountryScope::restrictMatchesQuery($q))
                ->where('match_time', '>', now()->timestamp)
                ->where('status_id', MatchStateEnum::NOT_STARTED->value)
                ->limit(16)
                ->get();

            $teams = Team::query()
                ->select(['team_id', 'name'])
                ->tap(fn ($q) => ScrapingCountryScope::restrictTeamsQuery($q))
                ->orderBy('name')
                ->limit(12)
                ->get();

            $players = Player::query()
                ->select(['player_id', 'name'])
                ->tap(fn ($q) => ScrapingCountryScope::restrictPlayersQuery($q))
                ->orderBy('name')
                ->limit(12)
                ->get();
        } else {
            $leagues = Competition::query()
                ->select([
                    'competition_id',
                    'name',
                    'name_aa',
                    'name_nl',
                    'name_vi',
                    'name_pt',
                    'name_br',
                    'name_es',
                    'name_fr',
                    'name_de',
                    'logo',
                ])
                ->whereIn('competition_id', [
                    'jednm9whz0ryox8',
                    'l965mkyh32r1ge4',
                    '4zp5rzghp5q82w1',
                    'j1l4rjnhx9m7vx5',
                    'gy0or5jhgwpqwzv',
                    'kdj2ryohnkq1zpg',
                    'gy0or5jhg6qwzv3',
                    'vl7oqdeheyr510j',
                    'gpxwrxlh2zryk0j',
                    '9vjxm8gh22r6odg',
                    '56ypq3nh1wmd7oj',
                    '9vjxm8ghx2r6odg',
                    'yl5ergphp0xr8k0',
                    '56ypq3nh0xmd7oj',
                    'z8yomo4h7wq0j6l',
                    'p4jwq2gh754m0ve',
                ])
                ->orderedBySequence()
                ->get();

            $nextMatches = FootballMatch::query()
                ->select('match_id', 'home_team_id', 'away_team_id', 'status_id', 'kickoff_time')
                ->with(['homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr', 'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de'])
                ->where('match_time', '>', now()->timestamp)
                ->where('status_id', MatchStateEnum::NOT_STARTED->value)
                ->limit(16)
                ->get();

            $teams = Team::query()
                ->select(['team_id', 'name'])
                ->whereIn('team_id', [
                    'e4wyrn4h127q86p',
                    'yl5ergphjy2r8k0',
                    'p4jwq2ghdeom0ve',
                    'l965mkyh98gr1ge',
                    'gpxwrxlhwl4ryk0',
                    'p4jwq2ghd57m0ve',
                    '56ypq3nhdpymd7o',
                    'z318q66hdd1qo9j',
                    'e4wyrn4h111q86p',
                    'e4wyrn4hn4dq86p',
                    '9dn1m1ghzl2moep',
                    '4zp5rzghvdoq82w',
                ])
                ->orderBy('name')
                ->get();

            $players = Player::query()
                ->select(['player_id', 'name'])
                ->whereIn('player_id', [
                    'p3glrw7hv73qdyj',
                    'ednm9wh9z22ryox',
                    'pxwrxlh1o1yryk0',
                    'n54qllhx11oqvy9',
                    'y0or5jheolzqwzv',
                    '3glrw7hypxxqdyj',
                    '4wyrn4hv7ydq86p',
                    'pxwrxlhze0dryk0',
                    'ednm9whev1dryox',
                    'l7oqdehe9j7r510',
                    'x7lm7ph0854om2w',
                    '3glrw7hj71ldqdy',
                ])
                ->orderBy('name')
                ->get();
        }

        $data = [
            'leagues' => $leagues,
            'next_matches' => $nextMatches,
            'teams' => $teams,
            'players' => $players,
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
