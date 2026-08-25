<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\SeasonTeamStatisticScrapperJob;
use Illuminate\Console\Command;

class SeasonTeamStatisticScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:season-team-statistics-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the season team statistics data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new SeasonTeamStatisticScrapperJob());
    }
}
