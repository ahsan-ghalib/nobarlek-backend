<?php

namespace App\Console\Commands\DataScrapping;

use App\Models\Season;
use App\Services\DataScrapping\SeasonPlayerStatisticsScrapingService;
use App\Services\DataScrapping\SeasonPlayerTopScorerScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Console\Command;

/**
 * The hourly season-stat jobs only refresh seasons that appeared in the data-update feed
 * during the last hour, so a season with no recent change never gets scorers at all.
 * This pulls the full player and shooter tables for current seasons.
 */
class SeasonPlayerLeadersScrapperCommand extends Command
{
    protected $signature = 'data-scrapping:season-player-leaders
                            {--season= : Only import this season ID}
                            {--competition= : Only import current seasons of this competition ID}';

    protected $description = 'Import full player statistics and top scorers for current seasons';

    public function handle(): int
    {
        $query = Season::query()->orderBy('id');

        if ($seasonId = $this->option('season')) {
            $query->where('season_id', $seasonId);
        } else {
            $query->where('is_current', true);
        }

        if ($competitionId = $this->option('competition')) {
            $query->where('competition_id', $competitionId);
        }

        ScrapingCountryScope::restrictSeasonsQuery($query);

        $count = (clone $query)->count();
        if ($count === 0) {
            $this->warn('No seasons to import.');

            return self::SUCCESS;
        }

        $this->info("Importing player leaders for {$count} seasons.");
        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $query->chunkById(100, function ($seasons) use ($progress): void {
            foreach ($seasons as $season) {
                $params = ['uuid' => $season->season_id];
                (new SeasonPlayerStatisticsScrapingService)->scrapSeasonPlayerStatisticData($params);
                (new SeasonPlayerTopScorerScrapingService)->scrapSeasonTopScorerData($params);
                $progress->advance();
            }
        });

        $progress->finish();
        $this->newLine(2);
        $this->info('Player leaders import completed.');

        return self::SUCCESS;
    }
}
