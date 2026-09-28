<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Services\DataScrapping\HeadToHeadDataScrapingService;
use App\Services\DataScrapping\RealtimeMatchDataScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * One API call per match whose data changed, so a busy minute takes several minutes.
 * Unique: at most one queued/running copy, instead of a new one piling up every minute
 * and occupying every worker.
 */
class HeadToHeadDataDataScrapperJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const LAST_RUN_KEY = 'scraper:head-to-head:last-run';

    /** Lock expires even if a worker dies mid-run. */
    public int $uniqueFor = 900;

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
        // Cover everything since the previous run, since runs no longer happen every minute.
        $since = Cache::get(self::LAST_RUN_KEY, $this->time);
        $startedAt = now()->timestamp;

        $matchIds = FootballData::query()
            ->select(['match_id'])
            ->whereNotNull('match_id')
            ->where('data_type', '=', DataUpdatesTypeEnum::SINGLE_MATCH_LINEUP->value)
            ->where('updated_at', '>', Carbon::createFromTimestamp((int) $since))
            ->get();

        foreach ($matchIds as $matchId) {
            (new HeadToHeadDataScrapingService())->scrapHeadToHeadData(['uuid' => $matchId->match_id]);
        }

        Cache::put(self::LAST_RUN_KEY, $startedAt, now()->addDay());
    }
}
