<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\MatchScrapperJob;
use App\Jobs\MatchTeamStatisticScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\MatchScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class MatchTeamStatisticScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:match-team-statistics-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the matches team statistics data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new MatchTeamStatisticScrapperJob());
    }
}
