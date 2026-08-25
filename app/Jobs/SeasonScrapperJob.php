<?php

namespace App\Jobs;

use App\Services\DataScrapping\SeasonScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SeasonScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subMinute(15)->timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new SeasonScrapingService())->scrapSeasonData(['time' => $this->time]);
    }
}
