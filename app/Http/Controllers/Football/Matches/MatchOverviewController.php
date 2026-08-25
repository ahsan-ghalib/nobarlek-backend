<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Models\MatchTeamStatistic;
use App\Support\MatchStreamPresenter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch)
    {
        $footballMatch->load([
            'matchInformation',
            'competition:competition_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'homeCoach:coach_id,name,logo',
            'awayCoach:coach_id,name,logo',
            'referee:referee_id,name,logo',
            'venue:venue_id,name',
            'matchIncidents' => fn ($query) => $query->latest('time'),
            'matchChartStatistic',
            'matchStatistics',
            'stream',
        ]);

        MatchStreamPresenter::attach($footballMatch);

        $footballMatch->append(['meta_keywords']);

        // Older scraper rows stored timeline as JSON inside JSON. Normalize those
        // records at the API boundary while newly scraped rows use the array cast.
        if ($footballMatch->matchChartStatistic) {
            $timeline = $footballMatch->matchChartStatistic->timeline;
            for ($attempt = 0; $attempt < 2 && is_string($timeline); $attempt++) {
                $decoded = json_decode($timeline, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $timeline = [];
                    break;
                }
                $timeline = $decoded;
            }
            $footballMatch->matchChartStatistic->timeline = is_array($timeline) ? $timeline : [];
        }

        // Attach player logos for match incidents (Substitution needs in/out player images).
        $incidents = $footballMatch->matchIncidents;
        if ($incidents && $incidents->count() > 0) {
            $ids = $incidents
                ->flatMap(function ($i) {
                    return [
                        $i->player_id,
                        $i->assist1_id,
                        $i->in_player_id,
                        $i->out_player_id,
                    ];
                })
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => trim($v))
                ->unique()
                ->values()
                ->all();

            if (count($ids) > 0) {
                $players = Player::query()
                    ->select(['player_id', 'logo', 'short_name'])
                    ->whereIn('player_id', $ids)
                    ->get()
                    ->keyBy('player_id');

                foreach ($incidents as $incident) {
                    $pid = is_string($incident->player_id) ? trim($incident->player_id) : '';
                    $assistId = is_string($incident->assist1_id) ? trim($incident->assist1_id) : '';
                    $inId = is_string($incident->in_player_id) ? trim($incident->in_player_id) : '';
                    $outId = is_string($incident->out_player_id) ? trim($incident->out_player_id) : '';

                    $incident->player_logo = $pid && isset($players[$pid]) ? $players[$pid]->logo : null;
                    $incident->in_player_logo = $inId && isset($players[$inId]) ? $players[$inId]->logo : null;
                    $incident->out_player_logo = $outId && isset($players[$outId]) ? $players[$outId]->logo : null;

                    $incident->player_short_name = $pid && isset($players[$pid])
                        ? $players[$pid]->short_name
                        : null;
                    $incident->assist1_short_name = $assistId && isset($players[$assistId])
                        ? $players[$assistId]->short_name
                        : null;
                    $incident->in_player_short_name = $inId && isset($players[$inId])
                        ? $players[$inId]->short_name
                        : null;
                    $incident->out_player_short_name = $outId && isset($players[$outId])
                        ? $players[$outId]->short_name
                        : null;
                }
            }
        }

        $query = MatchTeamStatistic::query()
            ->where('match_id', '=', $footballMatch->match_id)
            ->latest();

        $homeTeamStatistic = (clone $query)
            ->where('team_id', '=', $footballMatch->home_team_id)
            ->first();

        $awayTeamStatistic = (clone $query)
            ->where('team_id', '=', $footballMatch->away_team_id)
            ->first();

        $footballMatch->home_team_statistic = $homeTeamStatistic;
        $footballMatch->away_team_statistic = $awayTeamStatistic;

        return $this->jsonResponse(collect($footballMatch), Response::HTTP_OK);

    }
}
