<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionSeasonResultsController extends Controller
{
    public function __invoke(
        Request $request,
        Competition $competition,
        Season $season
    ): JsonResponse {
        $selectedSeason = isset($season->season_id)
            ? $season
            : $competition->currentSeason;

        abort_if($selectedSeason === null, Response::HTTP_NOT_FOUND);
        abort_unless(
            $selectedSeason->competition_id === $competition->competition_id,
            Response::HTTP_NOT_FOUND
        );

        $matches = $competition->matches()
            ->with([
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,logo',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,logo',
                'matchInformation',
            ])
            ->where('season_id', $selectedSeason->season_id)
            ->where('status_id', MatchStateEnum::END->value)
            ->when(
                $request->filled('stage_id'),
                fn ($query) => $query->where('stage_id', (string) $request->string('stage_id'))
            )
            ->orderByDesc('match_time')
            ->get();

        $rounds = $matches
            ->groupBy(fn ($match) => (int) $match->round_num)
            ->sortKeysDesc(SORT_NUMERIC)
            ->map(fn ($roundMatches, $round) => [
                'round' => (int) $round,
                'matches' => $roundMatches->values(),
            ])
            ->values();

        return $this->jsonResponse([
            'season' => [
                'season_id' => $selectedSeason->season_id,
                'year' => $selectedSeason->year,
                'is_current' => (bool) $selectedSeason->is_current,
            ],
            'total_matches' => $matches->count(),
            'total_rounds' => $rounds->count(),
            'rounds' => $rounds,
        ], Response::HTTP_OK);
    }
}
