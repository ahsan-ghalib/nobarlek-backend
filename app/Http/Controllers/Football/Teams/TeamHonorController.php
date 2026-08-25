<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamHonorController extends Controller
{
    /**
     * Display a listing of the team honors.
     */
    public function __invoke(Request $request, Team $team)
    {
        $honors = $team->teamHonors()
            ->select([
                'honors.name as honor_name',
                'honors.logo as honor_logo',
                'seasons.year as season_year',
                'team_honors.season as season',
                'competitions.name as competition_name',
            ])
            ->leftJoin('honors', 'team_honors.honor_id', '=', 'honors.honor_id')
            ->leftJoin('competitions', 'team_honors.competition_id', '=', 'competitions.competition_id')
            ->leftJoin('seasons', 'team_honors.season_id', '=', 'seasons.season_id')
            ->whereNotNull('competitions.name')
            ->orderByDesc('seasons.year')
            ->orderByDesc('team_honors.season')
            ->get()
            ->map(function ($row) {
                $honorName = trim((string) ($row->honor_name ?? ''));
                $competitionName = trim((string) ($row->competition_name ?? ''));
                $row->group_name = ($honorName !== '' && strcasecmp($honorName, 'Trophy') !== 0)
                    ? $honorName
                    : ($competitionName !== '' ? $competitionName : 'Trofi');

                return $row;
            })
            ->groupBy('group_name');

        return $this->jsonResponse($honors, Response::HTTP_OK);
    }
}
