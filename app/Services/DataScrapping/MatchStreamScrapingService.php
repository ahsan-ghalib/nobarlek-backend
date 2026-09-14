<?php

namespace App\Services\DataScrapping;

use App\Models\MatchStream;
use App\Traits\FootballScoreApiTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MatchStreamScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Sync TheSports h5 video stream list into match_streams.
     * Rows missing from the API response are deleted (stream ended / dropped).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function scrapMatchStreamData(array $params = []): array
    {
        $response = $this->callApi('/video/h5/stream/list', $params);

        if (($response['code'] ?? -1) !== 0 && ! isset($response['results'])) {
            Log::warning('Match stream scrape failed', [
                'code' => $response['code'] ?? null,
                'error' => $response['error'] ?? null,
            ]);

            return $response['query'] ?? ['total' => 0, 'error' => $response['error'] ?? 'unknown'];
        }

        $footballSportId = (int) config('streaming.football_sport_id', 1);
        $now = Carbon::now();
        $seenMatchIds = [];

        try {
            $rows = collect($response['results'] ?? [])
                ->filter(fn ($row) => is_array($row) && isset($row['match_id']))
                ->filter(fn ($row) => (int) ($row['sport_id'] ?? 0) === $footballSportId)
                ->map(function (array $row) use ($now, &$seenMatchIds) {
                    $matchId = (string) $row['match_id'];
                    $seenMatchIds[] = $matchId;

                    return [
                        'match_id' => $matchId,
                        'sport_id' => (int) ($row['sport_id'] ?? 1),
                        'match_time' => isset($row['match_time']) ? (int) $row['match_time'] : null,
                        'synced_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ];
                })
                ->values();

            if ($rows->isNotEmpty()) {
                MatchStream::query()->upsert(
                    $rows->all(),
                    ['match_id'],
                    ['sport_id', 'match_time', 'synced_at', 'updated_at']
                );
            }

            // Delete local rows no longer present in TheSports list.
            if ($seenMatchIds === []) {
                MatchStream::query()->where('sport_id', $footballSportId)->delete();
            } else {
                MatchStream::query()
                    ->where('sport_id', $footballSportId)
                    ->whereNotIn('match_id', $seenMatchIds)
                    ->delete();
            }

            return [
                'total' => $rows->count(),
                'synced' => count($seenMatchIds),
            ];
        } catch (\Throwable $exception) {
            Log::error('Match stream scrape exception', [
                'message' => $exception->getMessage(),
            ]);

            return ['total' => 0, 'error' => $exception->getMessage()];
        }
    }
}
