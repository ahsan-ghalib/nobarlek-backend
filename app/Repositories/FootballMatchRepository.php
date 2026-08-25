<?php

namespace App\Repositories;

use App\Enums\MatchScheduleStatusEnum;
use App\Enums\MatchStateEnum;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Repositories\Interfaces\FootballMatchInterface;
use App\Support\MatchTimeQuery;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FootballMatchRepository implements FootballMatchInterface
{
    private $model;

    public function __construct()
    {
        $this->model = new FootballMatch();
    }

    public function getSingleTeamMatches(Team $team, MatchScheduleStatusEnum $statusEnum, string $month): Builder
    {
        $start = Carbon::parse($month)->startOfMonth()->timestamp;
        $end = Carbon::parse($month)->endOfMonth()->timestamp;

        $query = $this->model
            ->with([
                'matchInformation:match_id,away_team_score,home_team_score',
                'homeTeam',
                'awayTeam',
                'competition',
            ])
            ->when(
                $statusEnum->value === MatchScheduleStatusEnum::Schedule->value,
                fn (Builder $query) => $query->whereIn('status_id', [
                    MatchStateEnum::NOT_STARTED->value,
                    MatchStateEnum::TO_BE_DETERMINED->value,
                    MatchStateEnum::DELAY->value,
                ])
            )
            ->when($statusEnum->value === MatchScheduleStatusEnum::Result->value, fn (Builder $query) => $query->where('status_id', '=', MatchStateEnum::END->value))
            ->where(fn (Builder $query) => $query->where('home_team_id', '=', $team->team_id)
                ->orWhere('away_team_id', '=', $team->team_id)
            );

        return MatchTimeQuery::applyBetween($query, $start, $end)
            ->when(
                $statusEnum->value === MatchScheduleStatusEnum::Schedule->value,
                fn (Builder $query) => $query->orderBy('match_time'),
                fn (Builder $query) => $query->orderByDesc('match_time'),
            )
            ->when(ScrapingCountryScope::shouldApply(), fn (Builder $query) => ScrapingCountryScope::restrictMatchesQuery($query));
    }

    public function getSingleTeamMatchesOutcomes(string $teamId): Builder
    {
        return $this->model
            ->select([
                'football_matches.match_time',
                'football_matches.kickoff_time',
                'football_matches.match_id',
                'football_matches.status_id',
                'football_matches.home_scores',
                'football_matches.away_scores',
                'competitions.name as competition_name',
                'competitions.logo as competition_logo',
                'home_team.team_id as home_team_id',
                'home_team.name as home_team_name',
                'home_team.logo as home_team_logo',
                'match_information.home_team_score',
                'match_information.home_team_halftime_score',
                'away_team.team_id as away_team_id',
                'away_team.name as away_team_name',
                'away_team.logo as away_team_logo',
                'match_information.away_team_score',
                'match_information.away_team_halftime_score',
            ])
            ->selectRaw('
                    CASE
                        WHEN (football_matches.home_team_id = ? AND match_information.home_team_score > match_information.away_team_score) OR (football_matches.away_team_id = ? AND match_information.away_team_score > match_information.home_team_score) THEN "Win"
                        WHEN (football_matches.home_team_id = ? AND match_information.home_team_score < match_information.away_team_score) OR (football_matches.away_team_id = ? AND match_information.away_team_score < match_information.home_team_score) THEN "Loss"
                        WHEN (football_matches.home_team_id = ? AND match_information.home_team_score = match_information.away_team_score) OR (football_matches.away_team_id = ? AND match_information.away_team_score = match_information.home_team_score) THEN "Draw"
                        ELSE "-"
                    END AS match_outcome
                ', [$teamId, $teamId, $teamId, $teamId, $teamId, $teamId]
            )
            ->latest('football_matches.match_time')
            ->leftJoin('competitions', 'football_matches.competition_id', '=', 'competitions.competition_id')
            ->leftJoin('teams as home_team', 'football_matches.home_team_id', '=', 'home_team.team_id')
            ->leftJoin('teams as away_team', 'football_matches.away_team_id', '=', 'away_team.team_id')
            ->leftJoin('match_information', 'football_matches.match_id', '=', 'match_information.match_id')
            ->when(ScrapingCountryScope::shouldApply(), fn (Builder $query) => ScrapingCountryScope::restrictMatchesQuery($query, 'football_matches.competition_id'));
    }
}
