<?php

namespace App\Jobs;

use App\Enums\DataUpdatesTypeEnum;
use App\Enums\MatchStateEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Services\DataScrapping\SingleMatchLineupDataScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SingleMatchLineUpsScrapperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    protected string $time;

    protected bool $live;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $live = false)
    {
        $this->live = $live;
        $this->time = now()->subHour()->startOfMinute()->toDateTimeString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $matches = $this->live
            ? $this->liveMatches()
            : $this->recentlyUpdatedMatches()
                ->merge($this->liveMatches())
                ->unique('match_id')
                ->values();

        foreach ($matches as $matchId) {
            (new SingleMatchLineupDataScrapingService())->scrapRealtimeMatchData(['uuid' => $matchId->match_id]);
            sleep(1);
        }
    }

    /**
     * Matches whose lineup TheSports flagged as updated in the last hour.
     */
    private function recentlyUpdatedMatches()
    {
        $matchIds = FootballData::query()
            ->select(['match_id'])
            ->whereNotNull('match_id')
            ->where('match_id', '!=', '')
            ->where('data_type', '=', DataUpdatesTypeEnum::SINGLE_MATCH_LINEUP->value)
            ->where('updated_at', '>', $this->time)
            ->pluck('match_id')
            ->toArray();

        return $this->scopedMatches()
            ->whereIn('match_id', $matchIds)
            ->where('lineup', '=', 1)
            ->get();
    }

    /**
     * Matches in the first half, half-time, second half, extra time, or penalties.
     */
    private function liveMatches()
    {
        return $this->scopedMatches()
            ->whereIn('status_id', [
                MatchStateEnum::FIRST_HALF->value,
                MatchStateEnum::HALF_TIME->value,
                MatchStateEnum::SECOND_HALF->value,
                MatchStateEnum::OVERTIME->value,
                MatchStateEnum::OVERTIME_DEPRECATED->value,
                MatchStateEnum::PENALTY_SHOOT_OUT->value,
            ])
            ->where('match_time', '>=', now()->subHours(4)->timestamp)
            ->where('lineup', '=', 1)
            ->get();
    }

    private function scopedMatches()
    {
        return FootballMatch::query()
            ->select(['match_id'])
            ->when(
                ScrapingCountryScope::shouldApply(),
                fn ($q) => $q->whereIn('competition_id', ScrapingCountryScope::allowedCompetitionIdsMerged()),
            );
    }
}
