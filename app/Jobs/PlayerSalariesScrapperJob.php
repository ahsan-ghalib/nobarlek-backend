<?php

namespace App\Jobs;

use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\PlayerSalariesScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlayerSalariesScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subDay()->startOfDay()->timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new PlayerSalariesScrapingService())->scrapPlayerSalariesData(['time' => $this->time]);
    }
}
