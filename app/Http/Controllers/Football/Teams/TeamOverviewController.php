<?php

namespace App\Http\Controllers\Football\Teams;

use App\Enums\MatchStateEnum;
use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\MatchLineUp;
use App\Models\Player;
use App\Models\Team;
use App\Support\ScrapingCountryScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        $team->load([
            'venue:venue_id,name,capacity',
            'coach:coach_id,name,logo',
            'competition:competition_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
        ])
            ->loadCount([
                'squads as total_squad',
            ]);

        $team->append(['meta_keywords']);

        // Avoid relying on inferred relationship keys; compute avg age via explicit join.
        $team->squad_players_avg_age = Player::query()
            ->join('team_squads', 'team_squads.player_id', '=', 'players.player_id')
            ->where('team_squads.team_id', '=', $team->team_id)
            ->avg('players.age');

        $lastMatch = FootballMatch::query()
            ->tap(fn ($q) => ScrapingCountryScope::restrictMatchesQuery($q))
            ->where(function ($query) use ($team) {
                $query->where('home_team_id', '=', $team->team_id)
                    ->orWhere('away_team_id', '=', $team->team_id);
            })
            ->with([
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            ])
            ->where('status_id', '=', MatchStateEnum::END->value)
            ->orderByDesc('match_time')
            ->first();

        if ($lastMatch === null) {
            $team->last_formation = [];
            $team->last_match = null;
        } else {
            $matchLineupsQuery = MatchLineUp::query()
                ->where('first', '=', true)
                ->where('x', '>', 0)
                ->where('y', '>', 0)
                ->where('match_id', '=', $lastMatch->match_id)
                ->with([
                    'player:player_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo,age,position',
                ]);

            if ($lastMatch->home_team_id === $team->team_id) {
                $matchLineupsQuery->where('type', '=', 'home');
            } elseif ($lastMatch->away_team_id === $team->team_id) {
                $matchLineupsQuery->where('type', '=', 'away');
            }

            $team->last_formation = $matchLineupsQuery->get();

            $team->last_match = [
                'match_id' => $lastMatch->match_id,
                'home_team' => $lastMatch->homeTeam?->name ?? '-',
                'home_team_id' => $lastMatch->home_team_id,
                'home_score' => is_array($lastMatch->home_scores) ? ($lastMatch->home_scores[0] ?? '-') : '-',
                'away_team' => $lastMatch->awayTeam?->name ?? '-',
                'away_team_id' => $lastMatch->away_team_id,
                'away_score' => is_array($lastMatch->away_scores) ? ($lastMatch->away_scores[0] ?? '-') : '-',
                'match_time' => $lastMatch->match_time,
                'formation' => $lastMatch->home_team_id === $team->team_id ? $lastMatch->home_formation : $lastMatch->away_formation,
            ];
        }

        $nextMatch = FootballMatch::query()
            ->tap(fn ($q) => ScrapingCountryScope::restrictMatchesQuery($q))
            ->where(function ($query) use ($team) {
                $query->where('home_team_id', '=', $team->team_id)
                    ->orWhere('away_team_id', '=', $team->team_id);
            })
            ->with([
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            ])
            ->where('status_id', '=', MatchStateEnum::NOT_STARTED->value)
            ->where('match_time', '>=', now()->timestamp)
            ->orderBy('match_time')
            ->first();

        if ($nextMatch === null) {
            $team->next_match = null;
        } else {
            $team->next_match = [
                'match_id' => $nextMatch->match_id,
                'home_team' => $nextMatch->homeTeam?->name ?? '-',
                'home_team_id' => $nextMatch->home_team_id,
                'away_team' => $nextMatch->awayTeam?->name ?? '-',
                'away_team_id' => $nextMatch->away_team_id,
                'match_time' => $nextMatch->match_time,
            ];
        }

        return $this->jsonResponse(collect($team), Response::HTTP_OK);
    }
}
