<?php

namespace App\Services\DataScrapping;

use App\Enums\MatchStateEnum;
use App\Enums\WeatherEnum;
use App\Models\FootballMatch;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

/**
 * TheSports {@see https://api.thesports.com/v1/football/match/diary} — date query (query.type = diary).
 *
 * Returns full schedule/results for a calendar day; same match shape as recent/list. Real-time minute data
 * should still use {@see RealtimeMatchDataScrapingService} / detail_live as per vendor notes.
 */
class MatchDiaryScrapingService
{
    use FootballScoreApiTrait;

    /** TheSports “invalid request / parameter” — wrong diary date key or format for this endpoint. */
    private const DIARY_API_CODE_INVALID_PARAMS = 100003;

    private array $weather;

    /** @var list<string> */
    private array $matchStatus;

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        $this->weather = WeatherEnum::values();
        $this->matchStatus = MatchStateEnum::values();
    }

    /**
     * Sync diary for one calendar day (API allows roughly ±30 days from “today”).
     *
     * @return array<string, mixed> query metadata on success, or error payload
     */
    public function scrapDiaryForDate(Carbon $calendarDay): array
    {
        $clamped = $this->clampToApiWindow($calendarDay);
        if ($clamped === null) {
            return ['error' => 'Date outside API diary window (±30 days from today).'];
        }

        return $this->fetchDiaryWithParamFallbacks($clamped);
    }

    /**
     * Sync tomorrow through today + N days (for the 30‑minute schedule). N defaults from config.
     *
     * @return list<array<string, mixed>> One result array per day (errors included per day)
     */
    public function scrapDiaryFutureDays(): array
    {
        $tz = $this->calendarTimezone();
        $n = max(1, (int) config('scraping.diary_future_days', 7));
        $out = [];
        for ($i = 1; $i <= $n; $i++) {
            $day = Carbon::today($tz)->addDays($i);
            $out[] = $this->scrapDiaryForDate($day);
        }

        return $out;
    }

    /**
     * Sync `/football/match/diary` for each calendar day in {@see calendarTimezone()},
     * from (today − ($days − 1)) through today. Rows are filtered by {@see ScrapingCountryScope}
     * when Indonesia-only scraping is enabled.
     *
     * @return list<array{date: string, result: array<string, mixed>}>
     */
    public function scrapDiaryPastDays(int $days = 30): array
    {
        $tz = $this->calendarTimezone();
        $days = max(1, $days);
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::today($tz)->subDays($i);
            $out[] = [
                'date' => $day->toDateString(),
                'result' => $this->scrapDiaryForDate($day),
            ];
        }

        return $out;
    }

    private function calendarTimezone(): string
    {
        $tz = config('scraping.diary_calendar_timezone');

        return is_string($tz) && $tz !== '' ? $tz : (string) config('app.timezone', 'UTC');
    }

    /**
     * API limit: from 30 days before today to 30 days after (use UTC for the window).
     */
    private function clampToApiWindow(Carbon $calendarDay): ?Carbon
    {
        $day = $calendarDay->copy()->timezone($this->calendarTimezone())->startOfDay();
        $min = Carbon::today('UTC')->subDays(30)->startOfDay();
        $max = Carbon::today('UTC')->addDays(30)->endOfDay();
        if ($day->lt($min) || $day->gt($max)) {
            return null;
        }

        return $day;
    }

    /**
     * @return array<string, scalar>
     */
    private function buildDiaryRequestParams(Carbon $calendarDay, ?string $dateParamOverride = null, ?string $formatOverride = null): array
    {
        $day = $calendarDay->copy()->timezone($this->calendarTimezone())->startOfDay();
        $format = $formatOverride ?? (string) config('scraping.diary_date_format', 'unix_start_of_day');
        $paramName = $dateParamOverride ?? (string) config('scraping.diary_date_param', 'time');
        if ($paramName === '') {
            $paramName = 'time';
        }

        $queryType = config('scraping.diary_query_type', 'diary');
        $queryType = is_string($queryType) && $queryType !== '' ? $queryType : 'diary';

        $value = match ($format) {
            'Y-m-d' => $day->format('Y-m-d'),
            'Ymd' => (int) $day->format('Ymd'),
            default => $day->getTimestamp(),
        };

        $extra = config('scraping.diary_extra_request_params');
        $base = is_array($extra) ? $extra : [];

        // `type` + date value must win over optional extras (same keys).
        return array_merge($base, [
            'type' => $queryType,
            $paramName => $value,
        ]);
    }

    /**
     * Ordered variants: config first, then common TheSports shapes when `date` + unix returns 100003.
     *
     * @return list<array<string, scalar>>
     */
    private function diaryRequestParamVariants(Carbon $calendarDay): array
    {
        $attempts = [
            [null, null],
            ['time', 'unix_start_of_day'],
            ['date', 'unix_start_of_day'],
            ['time', 'Y-m-d'],
            ['date', 'Y-m-d'],
            ['time', 'Ymd'],
            ['date', 'Ymd'],
        ];
        $seen = [];
        $out = [];
        foreach ($attempts as [$param, $fmt]) {
            $params = $this->buildDiaryRequestParams($calendarDay, $param, $fmt);
            $key = json_encode($params);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $params;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchDiaryWithParamFallbacks(Carbon $calendarDay): array
    {
        $lastError = [
            'error' => 'API error',
            'code' => null,
            'msg' => null,
        ];

        foreach ($this->diaryRequestParamVariants($calendarDay) as $params) {
            $matchData = $this->callApi('/football/match/diary', $params);

            if (! is_array($matchData)) {
                info('/football/match/diary: non-array or failed HTTP response');

                return ['error' => 'Request failed'];
            }

            $code = (int) ($matchData['code'] ?? -1);
            if ($code === 0) {
                return $this->upsertDiaryResponse($matchData);
            }

            $msg = $matchData['msg'] ?? $matchData['message'] ?? '';
            $msg = is_string($msg) ? trim($msg) : '';
            $suffix = $msg !== '' ? ' — '.$msg : '';
            info('/football/match/diary: API code '.(string) ($matchData['code'] ?? '').$suffix);

            $lastError = [
                'error' => 'API error',
                'code' => $matchData['code'] ?? null,
                'msg' => $msg !== '' ? $msg : null,
            ];

            if ($code !== self::DIARY_API_CODE_INVALID_PARAMS) {
                return $lastError;
            }
        }

        return $lastError;
    }

    /**
     * @param  array<string, mixed>  $matchData
     * @return array<string, mixed>
     */
    private function upsertDiaryResponse(array $matchData): array
    {
        $results = $matchData['results'] ?? [];
        if (! is_array($results)) {
            return is_array($matchData['query'] ?? null) ? $matchData['query'] : [];
        }

        try {
            collect($results)
                ->filter(fn ($match) => is_array($match) && ($match['id'] ?? '') !== '')
                ->filter(fn ($match) => ScrapingCountryScope::competitionIdAllowed($match['competition_id'] ?? null))
                ->map(fn (array $match) => $this->mapMatchRow($match))
                ->chunk(300)
                ->each(function ($chunk) {
                    FootballMatch::query()->upsert($chunk->values()->all(), ['match_id', 'competition_id']);
                });

            return is_array($matchData['query'] ?? null) ? $matchData['query'] : [];
        } catch (\Throwable $e) {
            info('/football/match/diary: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>  $match
     * @return array<string, mixed>
     */
    private function mapMatchRow(array $match): array
    {
        if (isset($match['environment']) && is_array($match['environment'])) {
            $w = $match['environment']['weather'] ?? 0;
            $match['environment']['weather'] = $this->weather[is_numeric($w) ? (int) $w : 0] ?? 'Unknown';
        }

        $coverage = is_array($match['coverage'] ?? null) ? $match['coverage'] : [];
        $round = is_array($match['round'] ?? null) ? $match['round'] : [];

        $statusIdx = is_numeric($match['status_id'] ?? null) ? (int) $match['status_id'] : 0;

        $updatedAt = $match['updated_at'] ?? null;
        if (is_numeric($updatedAt)) {
            $updatedAt = Carbon::createFromTimestamp((int) $updatedAt);
        } else {
            $updatedAt = now();
        }

        return [
            'match_id' => (string) ($match['id'] ?? ''),
            'season_id' => (string) ($match['season_id'] ?? ''),
            'competition_id' => (string) ($match['competition_id'] ?? ''),
            'home_team_id' => (string) ($match['home_team_id'] ?? ''),
            'away_team_id' => (string) ($match['away_team_id'] ?? ''),
            'status_id' => $this->matchStatus[$statusIdx] ?? MatchStateEnum::NOT_STARTED->value,
            'match_time' => $match['match_time'] ?? 0,
            'venue_id' => (string) ($match['venue_id'] ?? ''),
            'referee_id' => (string) ($match['referee_id'] ?? ''),
            'neutral' => $match['neutral'] ?? 0,
            'note' => (string) ($match['note'] ?? ''),
            'home_scores' => json_encode($match['home_scores'] ?? []),
            'away_scores' => json_encode($match['away_scores'] ?? []),
            'home_position' => (string) ($match['home_position'] ?? ''),
            'away_position' => (string) ($match['away_position'] ?? ''),
            'mlive' => (int) ($coverage['mlive'] ?? 0),
            'lineup' => (int) ($coverage['lineup'] ?? 0),
            'stage_id' => (string) ($round['stage_id'] ?? ''),
            'group_num' => $round['group_num'] ?? 0,
            'round_num' => $round['round_num'] ?? 0,
            'related_id' => ! empty($match['related_id']) ? (string) $match['related_id'] : null,
            'agg_score' => isset($match['agg_score']) && is_array($match['agg_score'])
                ? json_encode($match['agg_score'])
                : json_encode([]),
            'environment' => isset($match['environment']) && is_array($match['environment'])
                ? json_encode($match['environment'])
                : json_encode([]),
            'updated_at' => $updatedAt,
        ];
    }
}
