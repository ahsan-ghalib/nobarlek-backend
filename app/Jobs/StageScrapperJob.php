<?php

namespace App\Jobs;

use App\Services\DataScrapping\StageScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StageScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subHour()->timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new StageScrapingService())->scrapStageData(['time' => $this->time]);
    }
}
