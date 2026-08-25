<?php

namespace App\Jobs;

use App\Services\DataScrapping\CoachHonorScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CoachHonorScrapperJob implements ShouldQueue
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
        (new CoachHonorScrapingService())->scrapCoachHonorsData(['time' => $this->time]);
    }
}
