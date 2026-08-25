<?php

namespace App\Http\Controllers\Dashboard\Blogs;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchScheduleMatchRequest;
use App\Models\FootballMatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchScheduleMatchesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SearchScheduleMatchRequest $request): JsonResponse
    {
        $matches = FootballMatch::query()
            ->select(['id', 'kickoff_time', 'match_time','home_team_id', 'away_team_id', 'status_id'])
            ->with([
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->where('status_id', '=', MatchStateEnum::NOT_STARTED->value)
            ->whereBetween('match_time', [Carbon::parse($request->start_date)->timestamp, Carbon::parse($request->end_date)->timestamp])
            ->get();

        if ($matches->isEmpty()) {
            return response()->json([
                'data' => null,
                'message' => 'No data found',
            ], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'data' => $matches,
            'message' => 'Success fetching Matches',
        ], Response::HTTP_OK);
    }
}
