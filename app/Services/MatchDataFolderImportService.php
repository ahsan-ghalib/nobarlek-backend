<?php

namespace App\Services;

use App\Enums\EventReasonEnum;
use App\Enums\IncidentPositionEnum;
use App\Enums\IncidentReasonEnum;
use App\Enums\IncidentResultEnum;
use App\Enums\MatchStateEnum;
use App\Enums\TechnicalStatisticsEnum;
use App\Models\FootballMatch;
use App\Models\MatchIncidents;
use App\Models\MatchInjury;
use App\Models\MatchLineUp;
use App\Models\MatchPlayerStatistic;
use App\Models\MatchStatistic;
use App\Support\ScrapingCountryScope;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

/**
 * Imports JSON snapshots from `public/match-data/{competition}/{season}/{type}/{match_id}.txt`.
 *
 * Supported folders:
 * - match_lineup      → match_line_ups, football_matches formations, match_injuries
 * - match_statistics  → match_statistics, match_incidents, football_matches scores/status
 * - player_statistics → match_player_statistics
 */
class MatchDataFolderImportService
{
    public const TYPE_LINEUP = 'match_lineup';

    public const TYPE_MATCH_STATISTICS = 'match_statistics';

    public const TYPE_PLAYER_STATISTICS = 'player_statistics';

    private string $basePath;

    /** @var list<string> */
    private array $playerStatColumns;

    /** @var array<int, string> */
    private array $technicalStatistics;

    /** @var array<int, string> */
    private array $eventReason;

    /** @var array<int, string> */
    private array $incidentReason;

    /** @var array<int, string> */
    private array $incidentResult;

    /** @var array<int, string> */
    private array $positions;

    /** @var array<int, string> */
    private array $matchStatus;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? public_path('match-data');
        $this->playerStatColumns = (new MatchPlayerStatistic)->getFillable();
        $this->technicalStatistics = TechnicalStatisticsEnum::values();
        $this->eventReason = EventReasonEnum::values();
        $this->incidentReason = IncidentReasonEnum::values();
        $this->incidentResult = IncidentResultEnum::values();
        $this->positions = IncidentPositionEnum::values();
        $this->matchStatus = MatchStateEnum::values();
    }

    /**
     * @return array{processed: int, skipped: int, errors: int, by_type: array<string, int>, missing_directory?: bool}
     */
    public function run(?string $onlyType = null, ?callable $onProgress = null): array
    {
        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'by_type' => [
                self::TYPE_LINEUP => 0,
                self::TYPE_MATCH_STATISTICS => 0,
                self::TYPE_PLAYER_STATISTICS => 0,
            ],
        ];

        if (! is_dir($this->basePath)) {
            return array_merge($stats, ['missing_directory' => true]);
        }

        $finder = Finder::create()
            ->files()
            ->in($this->basePath)
            ->name('*.txt')
            ->sortByName();

        foreach ($finder as $file) {
            $type = $this->resolveType($file->getRelativePathname());
            if ($type === null || ($onlyType !== null && $onlyType !== $type)) {
                continue;
            }

            $matchId = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (! ScrapingCountryScope::matchIdAllowed($matchId)) {
                $stats['skipped']++;

                continue;
            }

            try {
                $raw = file_get_contents($file->getRealPath());
                $payload = json_decode($raw ?: '', true);
                if (! is_array($payload)) {
                    $stats['errors']++;

                    continue;
                }

                match ($type) {
                    self::TYPE_LINEUP => $this->importLineup($matchId, $payload),
                    self::TYPE_MATCH_STATISTICS => $this->importMatchStatistics($matchId, $payload),
                    self::TYPE_PLAYER_STATISTICS => $this->importPlayerStatistics($matchId, $payload),
                    default => null,
                };

                $stats['processed']++;
                $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;

                if ($onProgress !== null) {
                    $onProgress($matchId, $type, $stats);
                }
            } catch (\Throwable $exception) {
                $stats['errors']++;
                info("MatchDataFolderImport {$type}/{$matchId}: ".$exception->getMessage());
            }
        }

        return $stats;
    }

    private function resolveType(string $relativePath): ?string
    {
        $parts = explode('/', str_replace('\\', '/', $relativePath));
        $folder = $parts[count($parts) - 2] ?? null;

        return in_array($folder, [
            self::TYPE_LINEUP,
            self::TYPE_MATCH_STATISTICS,
            self::TYPE_PLAYER_STATISTICS,
        ], true) ? $folder : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function importLineup(string $matchId, array $data): void
    {
        $results = $this->unwrapResults($data);

        FootballMatch::query()
            ->where('match_id', '=', $matchId)
            ->update([
                'away_formation' => $results['away_formation'] ?? '-',
                'home_formation' => $results['home_formation'] ?? '-',
                'away_coach_id' => $results['coach_id']['away'] ?? '-',
                'home_coach_id' => $results['coach_id']['home'] ?? '-',
                'confirmed' => (int) ($results['confirmed'] ?? 0),
            ]);

        $lineupRows = $this->mergeSideRows($matchId, $results, 'lineup');
        if ($lineupRows !== []) {
            MatchLineUp::query()->upsert($lineupRows, ['match_id', 'player_id']);
        }

        $injuryRows = $this->mergeSideRows($matchId, $results, 'injury');
        if ($injuryRows !== []) {
            MatchInjury::query()->upsert($injuryRows, ['match_id', 'player_id']);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function importMatchStatistics(string $matchId, array $data): void
    {
        $matchId = (string) ($data['id'] ?? $matchId);
        $score = $data['score'] ?? [];
        $statusIdx = is_array($score) ? ($score[1] ?? 0) : 0;
        $statusId = $this->matchStatus[$statusIdx] ?? MatchStateEnum::NOT_STARTED->value;

        $stats = collect($data['stats'] ?? [])
            ->map(fn ($row) => [
                'type' => $this->technicalStatistics[$row['type'] ?? 0] ?? TechnicalStatisticsEnum::UNKNOWN->value,
                'away' => $row['away'] ?? 0,
                'home' => $row['home'] ?? 0,
            ])
            ->values()
            ->all();

        MatchStatistic::query()->updateOrCreate(
            ['match_id' => $matchId],
            ['stats' => json_encode($stats)],
        );

        $incidents = collect($data['incidents'] ?? [])
            ->map(function ($incident) use ($matchId) {
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

        if ($incidents->isNotEmpty()) {
            MatchIncidents::query()->upsert(
                $incidents->toArray(),
                ['match_id', 'player_id', 'type', 'time', 'in_player_id', 'out_player_id', 'in_player_name', 'out_player_name'],
            );
        }

        if (is_array($score)) {
            $matchUpdate = [
                'status_id' => $statusId,
                'home_scores' => json_encode($score[2] ?? []),
                'away_scores' => json_encode($score[3] ?? []),
            ];

            if ((int) ($score[4] ?? 0) > 0) {
                $matchUpdate['kickoff_time'] = (int) $score[4];
            }

            FootballMatch::query()
                ->where('match_id', '=', $matchId)
                ->update($matchUpdate);
        }
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $data
     */
    public function importPlayerStatistics(string $matchId, array $data): void
    {
        $rows = $this->unwrapPlayerStats($data);
        if ($rows === []) {
            return;
        }

        $allowed = array_flip(array_merge($this->playerStatColumns, ['match_id']));

        collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) use ($matchId, $allowed) {
                $row['match_id'] = $matchId;

                return array_intersect_key($row, $allowed);
            })
            ->filter(fn (array $row) => ! empty($row['player_id']) && ! empty($row['team_id']))
            ->chunk(200)
            ->each(function (Collection $chunk) {
                MatchPlayerStatistic::query()->upsert(
                    $chunk->values()->all(),
                    ['match_id', 'player_id', 'team_id'],
                );
            });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function unwrapResults(array $data): array
    {
        if (isset($data['results']) && is_array($data['results'])) {
            return $data['results'];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $data
     * @return list<array<string, mixed>>
     */
    private function unwrapPlayerStats(array $data): array
    {
        if (array_is_list($data)) {
            return $data;
        }

        if (isset($data['results']) && is_array($data['results']) && array_is_list($data['results'])) {
            return $data['results'];
        }

        if (isset($data['player_stats']) && is_array($data['player_stats'])) {
            return array_is_list($data['player_stats']) ? $data['player_stats'] : [];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $results
     * @return list<array<string, mixed>>
     */
    private function mergeSideRows(string $matchId, array $results, string $type): array
    {
        return $this->sideRows($results, $type, 'home', $matchId)
            ->merge($this->sideRows($results, $type, 'away', $matchId))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function sideRows(array $results, string $type, string $side, string $matchId): Collection
    {
        $rows = $results[$type][$side] ?? [];

        return collect(is_array($rows) ? $rows : [])
            ->map(function ($row) use ($side, $matchId) {
                if (! is_array($row)) {
                    return null;
                }

                $row = array_merge($row, ['type' => $side, 'match_id' => $matchId]);
                $row['player_id'] = $row['id'] ?? $row['player_id'] ?? '';
                unset($row['id'], $row['incidents']);

                return $row;
            })
            ->filter();
    }
}
