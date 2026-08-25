<?php

namespace Database\Seeders\HistoricalData;

use App\Enums\IncidentPositionEnum;
use App\Enums\MatchStateEnum;
use App\Enums\TechnicalStatisticsEnum;
use App\Enums\WeatherEnum;
use App\Models\FootballMatch;
use App\Models\MatchIncidents;
use App\Models\MatchStatistic;
use App\Services\HistoricalDataService;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatchDetailSeeder extends Seeder
{
    use FootballScoreApiTrait;
    private array $weather;
    private array $matchStatus;

    public function __construct()
    {
        ini_set('memory_limit', -1);
        $this->weather = WeatherEnum::values();
        $this->matchStatus = MatchStateEnum::values();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = $this->scrapMatchData(['page' => $pageNo]);
            if (isset($query['error'])) {
                info('MatchDetailSeeder stopped: '.$query['error']);
                break;
            }
            $pageNo++;
        } while (($query['total'] ?? 0) !== 0);
    }


    /**
     * Scrap and inset/update the Seasons from the sports api.
     * @param array $params
     */
    public function scrapMatchData(array $params = []): array
    {
        $matchData = $this->callApi('/football/match/list', $params);

        if (($matchData['code'] ?? -1) !== 0) {
            $errorMessage = $matchData['error'] ?? 'TheSports API error code '.(string) ($matchData['code'] ?? 'unknown');
            info('/football/match/list: '.$errorMessage);

            return array_merge($matchData['query'] ?? [], ['error' => $errorMessage, 'total' => 0]);
        }

        try {
            collect($matchData['results'] ?? [])
                ->filter(fn ($match) => is_array($match) && ScrapingCountryScope::competitionIdAllowed($match['competition_id'] ?? null))
                ->map(function ($match) {
                    if(isset($match['environment'])) {
                        $match['environment']['weather'] = $this->weather[$match['environment']['weather'] ?? 0] ?? 'Unknown';
                    }
                    return [
                        'match_id' => $match['id'],
                        'season_id' => $match['season_id'],
                        'competition_id' => $match['competition_id'],
                        'home_team_id' => $match['home_team_id'],
                        'away_team_id' => $match['away_team_id'],
                        'status_id' => $this->matchStatus[$match['status_id'] ?? 0],
                        'match_time' => $match['match_time'],
                        'venue_id' => $match['venue_id'],
                        'referee_id' => $match['referee_id'],
                        'neutral' => $match['neutral'],
                        'note' => $match['note'],
                        'home_scores' => json_encode($match['home_scores']),
                        'away_scores' => json_encode($match['away_scores']),
                        'home_position' => $match['home_position'],
                        'away_position' => $match['away_position'],
                        'mlive' => $match['coverage']['mlive'],
                        'lineup' => $match['coverage']['lineup'],
                        'stage_id' => $match['round']['stage_id'],
                        'group_num' => $match['round']['group_num'],
                        'round_num' => $match['round']['round_num'],
                        'related_id' => isset($match['related_id']) ?? '',
                        'agg_score' => isset($match['agg_score']) ? json_encode($match['agg_score']) : '',
                        'environment' => isset($match['environment']) ? json_encode($match['environment']) : '',
                        'updated_at' => Carbon::parse($match['updated_at']),
                    ];
                })
                ->chunk(300)
                ->map(function ($matchResults) {
                    FootballMatch::query()
                        ->upsert($matchResults->toArray(), ['match_id','competition_id']);
                });

            return $matchData['query'] ?? ['total' => 0];
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            info('/football/match/list:' . $errorMessage);
            return ['error' => $errorMessage, 'total' => 0];
        }
    }
}
