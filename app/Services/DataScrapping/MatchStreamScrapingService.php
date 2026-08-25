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
     * Sync TheSports video push stream list into match_streams.
     * Rows missing from the API response are deleted (stream ended / dropped).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function scrapMatchStreamData(array $params = []): array
    {
        $response = $this->callApi('/video/push/stream/list', $params);

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

                    $pushurl1 = filled($row['pushurl1'] ?? null) ? (string) $row['pushurl1'] : null;
                    $pushurl2 = filled($row['pushurl2'] ?? null) ? (string) $row['pushurl2'] : null;
                    $preferred = $pushurl2 ?: $pushurl1;

                    return [
                        'match_id' => $matchId,
                        'sport_id' => (int) ($row['sport_id'] ?? 1),
                        'match_time' => isset($row['match_time']) ? (int) $row['match_time'] : null,
                        'pushurl1' => $pushurl1,
                        'pushurl2' => $pushurl2,
                        'playback_url' => $this->buildPlaybackUrl($preferred),
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
                    ['sport_id', 'match_time', 'pushurl1', 'pushurl2', 'playback_url', 'synced_at', 'updated_at']
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

    /**
     * Convert RTMP push URL stream key into an HLS playback URL via config template.
     */
    public function buildPlaybackUrl(?string $pushUrl): ?string
    {
        if (! filled($pushUrl)) {
            return null;
        }

        $template = (string) config('streaming.playback_url_template', '');
        if ($template === '') {
            return null;
        }

        $streamKey = $this->extractStreamKey($pushUrl);
        if ($streamKey === null || $streamKey === '') {
            return null;
        }

        return str_replace('{stream_key}', $streamKey, $template);
    }

    /**
     * RTMP format: rtmp://{domain}/{endpoint}/{STREAM}
     */
    protected function extractStreamKey(string $pushUrl): ?string
    {
        $path = parse_url($pushUrl, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path)));

        return $segments === [] ? null : (string) end($segments);
    }
}
