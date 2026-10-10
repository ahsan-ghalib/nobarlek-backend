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

    protected string $time;

    protected ?string $matchId;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $matchId = null)
    {
        $this->matchId = $matchId !== null && trim($matchId) !== '' ? trim($matchId) : null;
        $this->time = now()->subHour()->startOfMinute();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->matchId !== null) {
            (new SingleMatchLineupDataScrapingService())->scrapRealtimeMatchData(['uuid' => $this->matchId]);

            return;
        }

        $matches = $this->recentlyUpdatedMatches()
            ->merge($this->liveMatches())
            ->unique('match_id')
            ->values();

        foreach ($matches as $matchId) {
            (new SingleMatchLineupDataScrapingService())->scrapRealtimeMatchData(['uuid' => $matchId->match_id]);
            sleep(1);
        }
    }

    /**
     * Follow the vendor's lineup update feed. Coverage can lag behind the
     * actual lineup response, so it is intentionally not used as a filter.
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
            ->get();
    }

    /** Poll matches around kickoff and while they are in play. */
    private function liveMatches()
    {
        return $this->scopedMatches()
            ->where(function ($query) {
                $query->whereIn('status_id', [
                    MatchStateEnum::FIRST_HALF->value,
                    MatchStateEnum::HALF_TIME->value,
                    MatchStateEnum::SECOND_HALF->value,
                    MatchStateEnum::OVERTIME->value,
                    MatchStateEnum::OVERTIME_DEPRECATED->value,
                    MatchStateEnum::PENALTY_SHOOT_OUT->value,
                ])->orWhereBetween('match_time', [
                    now()->subHours(1)->timestamp,
                    now()->addHours(2)->timestamp,
                ]);
            })
            ->limit(120)
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
