<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\MatchDiaryScrapperJob;
use Illuminate\Console\Command;

class MatchDiaryScrapperCommand extends Command
{
    protected $signature = 'data-scrapping:match-diary-scrapper-command
                            {--future : Sync tomorrow through the configured number of future days (30‑minute cadence)}
                            {--date= : Sync a single calendar day (Y-m-d); outside ±30 days from today is skipped}';

    protected $description = 'Sync schedule/results via TheSports football/match/diary (date query; vendor: ~10 min today, ~30 min future)';

    public function handle(): int
    {
        $date = $this->option('date');
        $dateStr = is_string($date) && $date !== '' ? $date : null;

        if ($dateStr !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            $this->error('Invalid --date (expected Y-m-d).');

            return self::FAILURE;
        }

        dispatch(new MatchDiaryScrapperJob(
            dateYmd: $dateStr,
            future: (bool) $this->option('future'),
        ))->onQueue('high');

        return self::SUCCESS;
    }
}
