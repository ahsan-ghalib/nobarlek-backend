<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Enums\CompetitionStatsTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use App\Models\SeasonPlayerStatistics;
use App\Models\SeasonTeamStatistics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionStatisticController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition, CompetitionStatsTypeEnum $statsTypeEnum, string $column, Season $season)
    {
        $statistics = ($statsTypeEnum->value === CompetitionStatsTypeEnum::TEAM->value ? SeasonTeamStatistics::query() : SeasonPlayerStatistics::query())
            ->where('season_id', '=', $season->season_id)
            ->where($column, '>', 0)
            ->orderByDesc($column)
            ->when($statsTypeEnum->value === CompetitionStatsTypeEnum::TEAM->value,
                fn (Builder $builder) => $builder->with('team:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo')->select(['team_id', $column]),
                fn (Builder $builder) => $builder->with('player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo')->select(['player_id', $column])
            )
            ->paginate(20);

        $teamColumns = ['matches', 'goals', 'penalty', 'assists', 'red_cards', 'yellow_cards', 'shots', 'shots_on_target', 'dribble', 'dribble_succ', 'clearances', 'blocked_shots', 'tackles', 'passes', 'passes_accuracy', 'key_passes', 'crosses', 'crosses_accuracy', 'long_balls', 'long_balls_accuracy', 'duels', 'duels_won', 'fouls', 'was_fouled', 'goals_against', 'interceptions', 'offsides', 'yellow2red_cards', 'corner_kicks', 'ball_possession', 'freekicks', 'freekick_goals', 'hit_woodwork', 'fastbreaks', 'fastbreak_shots', 'fastbreak_goals', 'poss_losts', 'saves', 'penalty_conceded', 'aerial_won', 'aerial_lost', 'ground_won', 'ground_lost', 'big_chance_created', 'big_chance_missed'];
        $playerColumns = ['matches', 'court', 'first', 'goals', 'penalty', 'assists', 'minutes_played', 'red_cards', 'yellow_cards', 'shots', 'shots_on_target', 'dribble', 'dribble_succ', 'clearances', 'blocked_shots', 'interceptions', 'tackles', 'passes', 'passes_accuracy', 'key_passes', 'crosses', 'crosses_accuracy', 'long_balls', 'long_balls_accuracy', 'duels', 'duels_won', 'dispossessed', 'fouls', 'was_fouled', 'offsides', 'yellow2red_cards', 'saves', 'punches', 'runs_out', 'runs_out_succ', 'good_high_claim', 'rating', 'freekicks', 'freekick_goals', 'hit_woodwork', 'fastbreaks', 'fastbreak_shots', 'fastbreak_goals', 'poss_losts', 'aerial_won', 'aerial_lost', 'ground_won', 'ground_lost', 'big_chance_created', 'big_chance_missed'];

        $columns = $statsTypeEnum->value === CompetitionStatsTypeEnum::TEAM->value ? $teamColumns : $playerColumns;

        return $this->jsonResponse(['statistics' => $statistics, 'columns' => $columns], Response::HTTP_OK);
    }
}
