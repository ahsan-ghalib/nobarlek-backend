<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Services\DataScrapping\HeadToHeadDataScrapingService;
use App\Services\DataScrapping\RealtimeMatchDataScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HeadToHeadDataDataScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subMinute()->timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
//        TODO: needs to discuss which head to head matches needs to updated
        $matchIds = FootballData::query()
            ->select(['match_id'])
            ->whereNotNull('match_id')
            ->where('data_type', '=', DataUpdatesTypeEnum::SINGLE_MATCH_LINEUP->value)
            ->where('updated_at', '>', $this->time)
            ->get();

        foreach ($matchIds as $matchId) {
            (new HeadToHeadDataScrapingService())->scrapHeadToHeadData(['uuid' => $matchId->match_id]);
        }
    }
}
