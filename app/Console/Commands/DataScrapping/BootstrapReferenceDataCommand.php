<?php

namespace App\Console\Commands\DataScrapping;

use App\Services\DataScrapping\CompetitionScrapingService;
use App\Services\DataScrapping\SeasonScrapingService;
use App\Services\DataScrapping\StageScrapingService;
use Illuminate\Console\Command;

/**
 * The scheduled competition/season/stage scrapers only fetch rows changed in the last
 * few minutes, so a fresh database never receives the full lists. This walks the
 * paginated TheSports lists once (1000 rows per page) to fill them in.
 */
class BootstrapReferenceDataCommand extends Command
{
    protected $signature = 'data-scrapping:bootstrap-reference-data
                            {--only= : Comma-separated subset: competitions,seasons,stages}
                            {--max-pages=500 : Safety cap on pages per list}';

    protected $description = 'Import the full competition, season and stage lists from TheSports';

    public function handle(): int
    {
        $lists = [
            'competitions' => fn (int $page) => (new CompetitionScrapingService)->scrapCompetitionData(['page' => $page]),
            'seasons' => fn (int $page) => (new SeasonScrapingService)->scrapSeasonData(['page' => $page]),
            'stages' => fn (int $page) => (new StageScrapingService)->scrapStageData(['page' => $page]),
        ];

        $only = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        $maxPages = max(1, (int) $this->option('max-pages'));

        foreach ($lists as $name => $fetch) {
            if ($only !== [] && ! in_array($name, $only, true)) {
                continue;
            }

            $rows = 0;
            for ($page = 1; $page <= $maxPages; $page++) {
                $query = $fetch($page);

                if (isset($query['error'])) {
                    $this->error("{$name} page {$page}: {$query['error']}");

                    return self::FAILURE;
                }

                $count = (int) ($query['total'] ?? 0);
                $rows += $count;
                $this->line("{$name}: page {$page}, {$count} rows");

                if ($count === 0) {
                    break;
                }
            }

            $this->info("{$name}: {$rows} rows imported.");
        }

        return self::SUCCESS;
    }
}
