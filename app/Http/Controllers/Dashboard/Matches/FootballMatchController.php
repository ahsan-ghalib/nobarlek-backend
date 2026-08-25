<?php

namespace App\Http\Controllers\Dashboard\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FootballMatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.matches');
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $matches = FootballMatch::query()
            ->select(['id', 'match_id', 'competition_id', 'home_team_id', 'away_team_id', 'match_time', 'seo_level'])
            ->with([
                'competition:competition_id,name',
                'homeTeam:team_id,name',
                'awayTeam:team_id,name',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('match_id', 'like', "%{$q}%")
                        ->orWhere('competition_id', 'like', "%{$q}%")
                        ->orWhere('home_team_id', 'like', "%{$q}%")
                        ->orWhere('away_team_id', 'like', "%{$q}%")
                        ->orWhereHas('competition', fn ($cq) => $cq->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('homeTeam', fn ($tq) => $tq->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('awayTeam', fn ($tq) => $tq->where('name', 'like', "%{$q}%"));
                });
            })
            ->latest('match_time')
            ->paginate(20);

        return response()->json([
            'data' => $matches,
            'message' => 'Successfully retrieved matches',
        ]);
    }

    public function show(FootballMatch $footballMatch): JsonResponse
    {
        $footballMatch->load([
            'competition:competition_id,name',
            'homeTeam:team_id,name',
            'awayTeam:team_id,name',
        ]);

        return response()->json([
            'data' => $footballMatch,
            'message' => 'Successfully retrieved match',
        ]);
    }
}

