<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Models\Season;
use App\Services\DataScrapping\SeasonTeamStatisticsScrapingService;
use App\Services\DataScrapping\SingleMatchLineupDataScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SeasonTeamStatisticScrapperJob implements ShouldQueue
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
            ->where('data_type', '=', DataUpdatesTypeEnum::SEASON_TEAM_STATISTICS->value)
            ->where('updated_at', '>', $this->time)
            ->pluck('season_id')
            ->toArray();

        $seasons = Season::query()
            ->select('season_id')
            ->whereIn('season_id', $seasonIds)
            ->where('is_current', '=', true)
            ->get();

        foreach ($seasons as $seasonId) {
            (new SeasonTeamStatisticsScrapingService())->scrapNewestSeasonTeamStatisticData(['uuid' => $seasonId->season_id]);
        }
    }
}
