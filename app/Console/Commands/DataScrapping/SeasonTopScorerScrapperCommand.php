<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\SeasonPlayerStatisticScrapperJob;
use App\Jobs\SeasonTeamStatisticScrapperJob;
use App\Jobs\SeasonTopScorerScrapperJob;
use Illuminate\Console\Command;

class SeasonTopScorerScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:season-top-scorer-statistics-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the season top scorer statistics data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new SeasonTopScorerScrapperJob());
    }
}
