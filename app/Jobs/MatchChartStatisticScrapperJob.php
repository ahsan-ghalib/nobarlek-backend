<?php

namespace App\Jobs;

use App\Enums\MatchStateEnum;
use App\Models\FootballMatch;
use App\Services\DataScrapping\MatchChartStatisticScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchChartStatisticScrapperJob implements ShouldQueue
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
        $matches = FootballMatch::query()
            ->select(['match_id'])
            ->whereIn('status_id', [
                MatchStateEnum::FIRST_HALF->value,
                MatchStateEnum::SECOND_HALF->value,
                MatchStateEnum::HALF_TIME->value,
                MatchStateEnum::OVERTIME->value,
                MatchStateEnum::OVERTIME_DEPRECATED->value,
                MatchStateEnum::PENALTY_SHOOT_OUT->value,
            ])
            ->whereBetween('updated_at', [now()->startOfDay(), now()->endOfDay()])
            ->when(
                ScrapingCountryScope::shouldApply(),
                fn ($q) => $q->whereIn('competition_id', ScrapingCountryScope::allowedCompetitionIdsMerged()),
            )
            ->get();

        foreach ($matches as $match) {
            (new MatchChartStatisticScrapingService())->scrapMatchChartStatisticData(['uuid' => $match->match_id]);
        }
    }
}
