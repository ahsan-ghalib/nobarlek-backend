<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Services\DataScrapping\TeamScrapingService;
use App\Services\DataScrapping\TeamStandingScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TeamStandingScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $teamIds;

    /**
     * Create a new job instance.
     */
//    public function __construct(array $teamIds)
//    {
//        $this->teamIds = $teamIds;
//    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new TeamStandingScrapingService())->scrapTeamStandingData();
    }
}
