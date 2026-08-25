<?php

namespace App\Services\DataScrapping;

use App\Enums\EventReasonEnum;
use App\Enums\IncidentPositionEnum;
use App\Enums\IncidentReasonEnum;
use App\Enums\IncidentResultEnum;
use App\Enums\MatchStateEnum;
use App\Enums\TechnicalStatisticsEnum;
use App\Models\FootballMatch;
use App\Models\MatchIncidents;
use App\Models\MatchStatistic;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Illuminate\Support\Facades\Log;

class RealtimeMatchDataScrapingService
{
    use FootballScoreApiTrait;

    private array $technicalStatistics;

    private array $eventReason;

    private array $incidentReason;

    private array $incidentResult;

    private array $positions;

    /** @var list<string> API status index => MatchStateEnum value */
    private array $matchStatus;

    public function __construct()
    {
        $this->technicalStatistics = TechnicalStatisticsEnum::values();
        $this->eventReason = EventReasonEnum::values();
        $this->incidentReason = IncidentReasonEnum::values();
        $this->incidentResult = IncidentResultEnum::values();
        $this->positions = IncidentPositionEnum::values();
        $this->matchStatus = MatchStateEnum::values();
    }

    /**
     * Scrap and inset/update the Venues from the sports api.
     */
    public function scrapRealtimeMatchData(array $params = []): array
    {
        $realtimeData = $this->callApi('/football/match/detail_live', $params);
        if (! is_array($realtimeData)) {
            Log::warning('scraper.detail_live.failed', ['params' => $params, 'reason' => 'non-array response']);

            return ['error' => 'Request failed'];
        }

        $code = $realtimeData['code'] ?? null;
        $results = $realtimeData['results'] ?? [];
        Log::info('scraper.detail_live.response', [
            'params' => $params,
            'code' => $code,
            'results_count' => is_array($results) ? count($results) : null,
        ]);

        try {
            $seen = 0;
            $withIncidents = 0;
            $zeroIncidentsButActive = 0;

            collect($results ?? [])
                ->map(function ($match) {
                    $matchId = $match['id'];
                    $coverage = is_array($match['coverage'] ?? null) ? $match['coverage'] : [];
                    $mlive = (int) ($coverage['mlive'] ?? 0);
                    $incidents = collect($match['incidents'] ?? [])->map(function ($incident) use ($matchId) {
                        return [
                            'match_id' => $matchId,
                            'type' => $this->technicalStatistics[$incident['type'] ?? 0] ?? TechnicalStatisticsEnum::UNKNOWN->value,
                            'position' => $this->positions[$incident['position'] ?? 0] ?? IncidentPositionEnum::NEUTRAL->value,
                            'time' => (int) ($incident['time'] ?? 0),
                            'player_id' => $incident['player_id'] ?? '',
                            'player_name' => $incident['player_name'] ?? '-',
                            'assist1_id' => $incident['assist1_id'] ?? '',
                            'assist1_name' => $incident['assist1_name'] ?? '-',
                            'assist2_id' => $incident['assist2_id'] ?? '',
                            'assist2_name' => $incident['assist2_name'] ?? '-',
                            'home_score' => $incident['home_score'] ?? 0,
                            'away_score' => $incident['away_score'] ?? 0,
                            'in_player_id' => $incident['in_player_id'] ?? '',
                            'in_player_name' => $incident['in_player_name'] ?? '-',
                            'out_player_id' => $incident['out_player_id'] ?? '',
                            'out_player_name' => $incident['out_player_name'] ?? '-',
                            'var_reason' => $this->incidentReason[$incident['var_reason'] ?? 0] ?? 'Other',
                            'var_result' => $this->incidentResult[$incident['var_result'] ?? 0] ?? 'Unknown',
                            'reason_type' => $this->eventReason[$incident['reason_type'] ?? 0] ?? 'Unknown',
                        ];
                    });

                    Log::info('scraper.detail_live.incidents', [
                        'match_id' => $matchId,
                        'incidents' => $incidents->count(),
                        'mlive' => $mlive,
                    ]);

                    $score = $match['score'] ?? [];
                    $statusIdx = $score[1] ?? 0;
                    $statusId = $this->matchStatus[$statusIdx] ?? MatchStateEnum::NOT_STARTED->value;

                    if ($incidents->count() === 0 && $statusId !== MatchStateEnum::NOT_STARTED->value && $mlive === 1) {
                        Log::warning('scraper.detail_live.zero_incidents', [
                            'match_id' => $matchId,
                            'status_id' => $statusId,
                            'mlive' => $mlive,
                        ]);
                    }

                    if ($incidents->count() !== 0) {
                        MatchIncidents::query()
                            ->upsert($incidents->toArray(), ['match_id', 'player_id', 'type', 'time', 'in_player_id', 'out_player_id',  'in_player_name', 'out_player_name']);
                        Log::info('scraper.detail_live.upsert_incidents', [
                            'match_id' => $matchId,
                            'incidents_count' => $incidents->count(),
                            'status_id' => $statusId,
                            'mlive' => $mlive,
                        ]);
                    }

                    $match['stats'] = collect($match['stats'] ?? [])->map(fn ($stats) => [
                        'type' => $this->technicalStatistics[$stats['type'] ?? 0] ?? TechnicalStatisticsEnum::UNKNOWN->value,
                        'away' => $stats['away'] ?? 0,
                        'home' => $stats['home'] ?? 0,
                    ]);

                    MatchStatistic::query()
                        ->updateOrCreate(['match_id' => $matchId], ['stats' => $match['stats']->all()]);

                    $matchUpdate = [
                        'status_id' => $statusId,
                        'home_scores' => json_encode($score[2] ?? []),
                        'away_scores' => json_encode($score[3] ?? []),
                    ];

                    if ((int) ($score[4] ?? 0) > 0) {
                        $matchUpdate['kickoff_time'] = (int) $score[4];
                    }

                    FootballMatch::query()
                        ->where('match_id', '=', $match['id'])
                        ->update($matchUpdate);
                });

            // NOTE: Any match-level aggregate logging would require collecting inside map/each.
            // Keep per-match logs only for "upsert" and "zero incidents but should exist" cases.

            return $realtimeData;
        } catch (\Exception $exception) {
            // TODO: trigger the notification
            $errorMessage = $exception->getMessage();
            Log::error('scraper.detail_live.exception', ['params' => $params, 'error' => $errorMessage]);

            return ['error' => $errorMessage];
        }
    }
}
