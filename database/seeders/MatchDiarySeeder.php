<?php

namespace Database\Seeders;

use App\Services\DataScrapping\MatchDiaryScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Pulls TheSports `/football/match/diary` for the last 30 calendar days (diary timezone from
 * `config('scraping.diary_calendar_timezone')`). With `SCRAPING_INDONESIA_ONLY=true`, only in-scope
 * competitions are upserted into `football_matches`.
 *
 * Run: `php artisan db:seed --class=MatchDiarySeeder`
 *
 * Prerequisite: competitions (and scope) so {@see \App\Support\ScrapingCountryScope::competitionIdAllowed} matches diary rows.
 */
class MatchDiarySeeder extends Seeder
{
    use WithoutModelEvents;

    private const PAST_DAYS = 30;

    public function run(): void
    {
        $rows = app(MatchDiaryScrapingService::class)->scrapDiaryPastDays(self::PAST_DAYS);

        foreach ($rows as $row) {
            $result = $row['result'];
            if (isset($result['error']) && $this->command !== null) {
                $msg = (string) $result['error'];
                $code = $result['code'] ?? null;
                $suffix = $code !== null ? ' (code: '.(string) $code.')' : '';
                $this->command->warn("Match diary {$row['date']}: {$msg}{$suffix}");
            }
        }
    }
}
