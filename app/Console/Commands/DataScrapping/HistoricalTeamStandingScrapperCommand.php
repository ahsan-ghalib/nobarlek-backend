<?php

namespace App\Console\Commands\DataScrapping;

use App\Models\Season;
use App\Services\DataScrapping\TeamStandingScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Console\Command;

class HistoricalTeamStandingScrapperCommand extends Command
{
    protected $signature = 'data-scrapping:historical-team-standings
                            {--from=1900 : Import seasons from this year onward}
                            {--competition= : Limit the import to one competition ID}';

    protected $description = 'Import historical team standings from the AiScore API';

    public function handle(TeamStandingScrapingService $scraper): int
    {
        $fromYear = filter_var($this->option('from'), FILTER_VALIDATE_INT);

        if ($fromYear === false || $fromYear < 1900) {
            $this->error('The --from option must be a valid year.');

            return self::INVALID;
        }

        $query = Season::query()
            ->where('year', '>=', $fromYear)
            ->where('has_table', true)
            ->orderBy('id');

        if ($competitionId = $this->option('competition')) {
            $query->where('competition_id', $competitionId);
        }

        ScrapingCountryScope::restrictSeasonsQuery($query);

        $seasonCount = (clone $query)->count();

        if ($seasonCount === 0) {
            $this->warn("No table-enabled seasons found from {$fromYear} onward.");

            return self::SUCCESS;
        }

        $this->info("Importing standings for {$seasonCount} seasons from {$fromYear} onward.");
        $progress = $this->output->createProgressBar($seasonCount);
        $progress->start();

        $query->chunkById(100, function ($seasons) use ($scraper, $progress): void {
            foreach ($seasons as $season) {
                $scraper->scrapHistoricalTeamStandingData([
                    'uuid' => $season->season_id,
                ]);
                $progress->advance();
            }
        });

        $progress->finish();
        $this->newLine(2);
        $this->info('Historical standings import completed.');

        return self::SUCCESS;
    }
}
