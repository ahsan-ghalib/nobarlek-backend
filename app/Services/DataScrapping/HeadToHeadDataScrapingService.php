<?php

namespace App\Services\DataScrapping;

use App\Models\MatchInformation;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class HeadToHeadDataScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Venues from the sports api.
     */
    public function scrapHeadToHeadData(array $params = []): int|array
    {
        $headToHeadData = $this->callApi('/football/match/analysis', $params);

        try {
            if (is_array($headToHeadData) && 0 === ($headToHeadData['code'] ?? -1) && isset($headToHeadData['results']['info'])) {
                $matchInformation = $headToHeadData['results']['info'];
                if (ScrapingCountryScope::shouldApply()
                    && ! ScrapingCountryScope::competitionIdAllowed(isset($matchInformation[1]) ? (string) $matchInformation[1] : null)) {
                    return $headToHeadData['code'];
                }
                MatchInformation::query()
                    ->updateOrCreate([
                        'match_id' => $matchInformation[0],
                        'competition_id' => $matchInformation[1],
                    ], [
                        'match_status' => $matchInformation[2],
                        'match_time' => $matchInformation[3],
                        'kick_off' => $matchInformation[4],
                        'home_team_id' => $matchInformation[5][0],
                        'home_league_ranking' => $matchInformation[5][1],
                        'home_team_score' => $matchInformation[5][2],
                        'home_team_halftime_score' => $matchInformation[5][3],
                        'home_team_red_cards' => $matchInformation[5][4],
                        'home_team_yellow_cards' => $matchInformation[5][5],
                        'home_team_corners' => $matchInformation[5][6],
                        'home_team_overtime_score' => $matchInformation[5][7],
                        'home_team_penalty_shootout_score' => $matchInformation[5][8],
                        'away_team_id' => $matchInformation[6][0],
                        'away_league_ranking' => $matchInformation[6][1],
                        'away_team_score' => $matchInformation[6][2],
                        'away_team_halftime_score' => $matchInformation[6][3],
                        'away_team_red_cards' => $matchInformation[6][4],
                        'away_team_yellow_cards' => $matchInformation[6][5],
                        'away_team_corners' => $matchInformation[6][6],
                        'away_team_overtime_score' => $matchInformation[6][7],
                        'away_team_penalty_shootout_score' => $matchInformation[6][8],
                        'asian_plate_home' => $matchInformation[7][0] ?? '-',
                        'european_plate' => $matchInformation[7][1] ?? '-',
                        'size_ball_plate' => $matchInformation[7][2] ?? '-',
                        'corner_plate' => $matchInformation[7][3] ?? '-',
                        'match_description' => $matchInformation[8][0] ?? '-',
                        'is_it_neutral' => $matchInformation[8][1] ?? '-',
                        'match_round' => $matchInformation[8][2] ?? '-',
                        'season_id' => $matchInformation[9][0] ?? '-',
                        'season_year' => $matchInformation[9][1] ?? '-',
                    ]);
            }

            return $headToHeadData['code'];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/match/analysis: '.$errorMessage);

            return ['error' => $errorMessage];
        }

    }
}
