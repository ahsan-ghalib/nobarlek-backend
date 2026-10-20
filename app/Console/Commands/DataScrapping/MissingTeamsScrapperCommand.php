<?php

namespace App\Console\Commands\DataScrapping;

use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\DataScrapping\TeamScrapingService;
use Illuminate\Console\Command;

/**
 * The daily team sync only asks for teams the vendor changed in the last day, so a
 * team that was never stored stays missing and its matches show no name or crest.
 * This fetches teams that nearby matches reference but the teams table lacks.
 */
class MissingTeamsScrapperCommand extends Command
{
    protected $signature = 'data-scrapping:missing-teams-scrapper-command
        {--days=14 : Look at matches this many days before and after today}
        {--dry-run : List the missing team IDs without fetching}';

    protected $description = 'Fetch teams referenced by matches but missing from the teams table';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $window = [now()->subDays($days)->timestamp, now()->addDays($days)->timestamp];

        $referenced = FootballMatch::query()
            ->whereBetween('match_time', $window)
            ->select('home_team_id as team_id')
            ->union(
                FootballMatch::query()
                    ->whereBetween('match_time', $window)
                    ->select('away_team_id as team_id'),
            )
            ->pluck('team_id')
            ->filter(fn ($id) => is_string($id) && trim($id) !== '')
            ->unique();

        $known = $referenced->chunk(1000)
            ->flatMap(fn ($ids) => Team::query()->whereIn('team_id', $ids->all())->pluck('team_id'))
            ->all();
        $missing = $referenced->diff($known)->values();

        if ($this->option('dry-run')) {
            $this->info("{$missing->count()} missing teams: ".$missing->implode(', '));

            return self::SUCCESS;
        }

        $service = new TeamScrapingService;
        foreach ($missing as $teamId) {
            $service->scrapTeamData(['uuid' => $teamId]);
        }

        $stillMissing = $missing->diff(Team::query()->whereIn('team_id', $missing->all())->pluck('team_id'));
        $this->info("Fetched ".($missing->count() - $stillMissing->count())." of {$missing->count()} missing teams.");
        if ($stillMissing->isNotEmpty()) {
            $this->warn('Vendor returned nothing for: '.$stillMissing->implode(', '));
        }

        return self::SUCCESS;
    }
}
