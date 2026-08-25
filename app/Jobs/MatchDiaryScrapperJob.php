<?php

namespace App\Jobs;

use App\Services\DataScrapping\MatchDiaryScrapingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchDiaryScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $dateYmd = null,
        public bool $future = false
    ) {
    }

    public function handle(MatchDiaryScrapingService $service): void
    {
        $tz = config('scraping.diary_calendar_timezone');
        $tz = is_string($tz) && $tz !== '' ? $tz : (string) config('app.timezone', 'UTC');

        if ($this->future) {
            $service->scrapDiaryFutureDays();

            return;
        }

        if ($this->dateYmd !== null && $this->dateYmd !== '') {
            $service->scrapDiaryForDate(Carbon::createFromFormat('Y-m-d', $this->dateYmd, $tz)->startOfDay());

            return;
        }

        $service->scrapDiaryForDate(Carbon::today($tz));
    }
}
