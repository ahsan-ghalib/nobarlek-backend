<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Models\SeasonPlayerStatistics;
use App\Services\DataScrapping\SeasonPlayerStatisticsScrapingService;
use App\Services\DataScrapping\SeasonPlayerTopScorerScrapingService;
use App\Services\DataScrapping\SingleMatchLineupDataScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SeasonTopScorerScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->time = now()->subHour()->startOfMinute();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $seasonIds = FootballData::query()
            ->select(['season_id'])
            ->whereNotNull('season_id')
            ->where('data_type', '=', DataUpdatesTypeEnum::SEASON_TOP_SCORER->value)
            ->where('updated_at', '>', $this->time)
            ->get();

        foreach ($seasonIds as $seasonId) {
            (new SeasonPlayerTopScorerScrapingService())->scrapNewestSeasonTopScorerData(['uuid' => $seasonId->season_id]);
        }
    }
}
