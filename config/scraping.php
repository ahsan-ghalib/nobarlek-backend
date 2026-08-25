<?php

/**
 * Scope TheSports sync jobs to Indonesian football (or any country allow-list).
 *
 * Set SCRAPING_INDONESIA_ONLY=true and either:
 * - SCRAPING_ALLOWED_COUNTRY_IDS with TheSports `country_id` value(s) for Indonesia, or
 * - Rely on `countries` rows whose name contains "Indonesia" (after country sync).
 *
 * Allowed competitions are rows already in `competitions` whose `country_id` is in that list, plus
 * SCRAPING_EXTRA_COMPETITION_IDS. Sync competitions first so the table contains the leagues you need.
 *
 * Public JSON under /api uses the same rules when indonesia_only is on (middleware + ScrapingCountryScope).
 *
 * @see https://www.thesports.com/docs/football
 */
return [
    'indonesia_only' => env('SCRAPING_INDONESIA_ONLY', false),

    /** Comma-separated TheSports `country_id` strings (recommended for production). */
    'allowed_country_ids' => array_values(array_filter(array_map('trim', explode(',', env('SCRAPING_ALLOWED_COUNTRY_IDS', ''))))),

    /** Always allow these competition UUIDs even if country row is missing. */
    'extra_competition_ids' => array_values(array_filter(array_map('trim', explode(',', env('SCRAPING_EXTRA_COMPETITION_IDS', ''))))),

    /**
     * {@see \App\Services\DataScrapping\MatchDiaryScrapingService} — `/football/match/diary` date query.
     *
     * `diary_query_type`: request `type` — must be `diary` for date-based schedule (vs `season` + `uuid`).
     *
     * `diary_date_param`: query key for the calendar day. TheSports commonly expects `time` (unix); `date` often returns code 100003.
     *
     * `diary_date_format`: how the date param value is sent.
     *   - unix_start_of_day (default): Unix timestamp at start of that calendar day in `diary_calendar_timezone`
     *   - Y-m-d: e.g. 2026-04-09
     *   - Ymd: integer e.g. 20260409
     */
    'diary_calendar_timezone' => env('SCRAPING_DIARY_CALENDAR_TZ', 'UTC'),

    'diary_query_type' => env('SCRAPING_DIARY_QUERY_TYPE', 'diary'),

    'diary_date_param' => env('SCRAPING_DIARY_DATE_PARAM', 'time'),

    'diary_date_format' => env('SCRAPING_DIARY_DATE_FORMAT', 'unix_start_of_day'),

    /** Optional extra query params merged last (JSON object in env), e.g. {} */
    'diary_extra_request_params' => json_decode((string) env('SCRAPING_DIARY_EXTRA_JSON', '{}'), true) ?: [],

    /** Number of days after today to refresh on the 30‑minute diary job (`--future`). */
    'diary_future_days' => (int) env('SCRAPING_DIARY_FUTURE_DAYS', 7),
];
