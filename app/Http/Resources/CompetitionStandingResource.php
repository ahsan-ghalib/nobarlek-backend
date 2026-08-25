<?php

namespace App\Http\Resources;

use App\Enums\MatchStateEnum;
use App\Models\FootballMatch;
use App\Support\MatchTimeQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionStandingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'standing_id' => $this->standing_id,
            'conference' => $this->conference,
            'group' => $this->group,
            'stage_id' => $this->stage_id,
            'team_id' => $this->team_id,
            'promotion_id' => $this->promotion_id,
            'points' => $this->points,
            'position' => $this->position,
            'deduct_points' => $this->deduct_points,
            'note' => $this->note,
            'total' => $this->total,
            'won' => $this->won,
            'draw' => $this->draw,
            'loss' => $this->loss,
            'goals' => $this->goals,
            'goals_against' => $this->goals_against,
            'goal_diff' => $this->goal_diff,
            'home_points' => $this->home_points,
            'home_position' => $this->home_position,
            'home_total' => $this->home_total,
            'home_won' => $this->home_won,
            'home_draw' => $this->home_draw,
            'home_loss' => $this->home_loss,
            'home_goals' => $this->home_goals,
            'home_goals_against' => $this->home_goals_against,
            'home_goal_diff' => $this->home_goal_diff,
            'away_points' => $this->away_points,
            'away_position' => $this->away_position,
            'away_total' => $this->away_total,
            'away_won' => $this->away_won,
            'away_draw' => $this->away_draw,
            'away_loss' => $this->away_loss,
            'away_goals' => $this->away_goals,
            'away_goals_against' => $this->away_goals_against,
            'away_goal_diff' => $this->away_goal_diff,
            'team' => $this->team,
            'last_five_matches' => $this->lastFiveMatches($request),
            'performance_stats' => $this->performanceStats($request),
        ];
    }

    /**
     * Statistics consumed by the league Pola, Over/Under and HT/FT tabs.
     * Score arrays use [full-time, half-time, ...].
     *
     * @return array<string, mixed>
     */
    private function performanceStats(Request $request): array
    {
        $teamId = (string) $this->team_id;
        $type = (string) ($request->query('type') ?: 'All');
        $query = FootballMatch::query()
            ->select(['home_team_id', 'away_team_id', 'home_scores', 'away_scores'])
            ->where('season_id', $this->season_id)
            ->where('status_id', MatchStateEnum::END->value);

        if ($type === 'home') {
            $query->where('home_team_id', $teamId);
        } elseif ($type === 'away') {
            $query->where('away_team_id', $teamId);
        } else {
            $query->where(fn ($q) => $q->where('home_team_id', $teamId)
                ->orWhere('away_team_id', $teamId));
        }

        $over = 0;
        $under = 0;
        $htFt = [];

        foreach ($query->get() as $match) {
            $homeFt = self::scoreAt($match->home_scores, 0);
            $awayFt = self::scoreAt($match->away_scores, 0);
            $homeHt = self::scoreAt($match->home_scores, 1);
            $awayHt = self::scoreAt($match->away_scores, 1);
            if ($homeFt === null || $awayFt === null) {
                continue;
            }

            ($homeFt + $awayFt) > 2.5 ? $over++ : $under++;

            if ($homeHt === null || $awayHt === null) {
                continue;
            }

            $isHome = (string) $match->home_team_id === $teamId;
            $halfResult = self::resultCode($isHome ? $homeHt : $awayHt, $isHome ? $awayHt : $homeHt);
            $fullResult = self::resultCode($isHome ? $homeFt : $awayFt, $isHome ? $awayFt : $homeFt);
            $key = $halfResult.'/'.$fullResult;
            $htFt[$key] = ($htFt[$key] ?? 0) + 1;
        }

        ksort($htFt);

        return [
            'over_2_5' => $over,
            'under_2_5' => $under,
            'ht_ft' => $htFt,
        ];
    }

    private static function scoreAt(mixed $scores, int $index): ?int
    {
        if (! is_array($scores) || ! isset($scores[$index]) || ! is_numeric($scores[$index])) {
            return null;
        }

        return (int) $scores[$index];
    }

    private static function resultCode(int $teamGoals, int $opponentGoals): string
    {
        return $teamGoals > $opponentGoals ? 'W' : ($teamGoals < $opponentGoals ? 'L' : 'D');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lastFiveMatches(Request $request): array
    {
        $teamId = (string) $this->team_id;
        $type = (string) ($request->query('type') ?: 'All');
        $timeExpr = MatchTimeQuery::unixExpression();

        $query = FootballMatch::query()
            ->select([
                'football_matches.match_id',
                'home_scores',
                'away_scores',
                'status_id',
                'match_time',
                'kickoff_time',
                'home_team_id',
                'away_team_id',
            ])
            ->with(['matchInformation:match_id,home_team_score,away_team_score'])
            ->where('season_id', '=', $this->season_id)
            ->where('status_id', '=', MatchStateEnum::END->value);

        if ($type === 'home') {
            $query->where('home_team_id', '=', $teamId);
        } elseif ($type === 'away') {
            $query->where('away_team_id', '=', $teamId);
        } else {
            $query->where(fn ($q) => $q->where('home_team_id', '=', $teamId)
                ->orWhere('away_team_id', '=', $teamId));
        }

        return $query
            ->orderByRaw("{$timeExpr} DESC")
            ->limit(5)
            ->get()
            ->map(function (FootballMatch $match) use ($teamId) {
                return [
                    'match_id' => $match->match_id,
                    'status_id' => $match->status_id,
                    'home_team_id' => $match->home_team_id,
                    'away_team_id' => $match->away_team_id,
                    'home_scores' => $match->home_scores,
                    'away_scores' => $match->away_scores,
                    'match_outcome' => self::matchOutcomeForTeam($match, $teamId),
                ];
            })
            ->values()
            ->all();
    }

    private static function matchOutcomeForTeam(FootballMatch $match, string $teamId): ?string
    {
        $homeId = (string) $match->home_team_id;
        $awayId = (string) $match->away_team_id;
        if ($homeId !== $teamId && $awayId !== $teamId) {
            return null;
        }

        $homeGoals = self::fullTimeHomeGoals($match);
        $awayGoals = self::fullTimeAwayGoals($match);
        if ($homeGoals === null || $awayGoals === null) {
            return null;
        }

        if ($homeId === $teamId) {
            if ($homeGoals > $awayGoals) {
                return 'W';
            }
            if ($homeGoals < $awayGoals) {
                return 'L';
            }

            return 'D';
        }

        if ($awayGoals > $homeGoals) {
            return 'W';
        }
        if ($awayGoals < $homeGoals) {
            return 'L';
        }

        return 'D';
    }

    private static function fullTimeHomeGoals(FootballMatch $match): ?int
    {
        $scores = $match->home_scores;
        if (is_array($scores) && isset($scores[0]) && is_numeric($scores[0])) {
            return (int) $scores[0];
        }

        $info = $match->matchInformation;
        if ($info !== null && is_numeric($info->home_team_score)) {
            return (int) $info->home_team_score;
        }

        return null;
    }

    private static function fullTimeAwayGoals(FootballMatch $match): ?int
    {
        $scores = $match->away_scores;
        if (is_array($scores) && isset($scores[0]) && is_numeric($scores[0])) {
            return (int) $scores[0];
        }

        $info = $match->matchInformation;
        if ($info !== null && is_numeric($info->away_team_score)) {
            return (int) $info->away_team_score;
        }

        return null;
    }
}
