<?php

namespace App\Jobs;

use App\Services\DataScrapping\RefereeScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefereeScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subDay()->timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new RefereeScrapingService())->scrapRefereeData(['time' => $this->time]);
    }
}
