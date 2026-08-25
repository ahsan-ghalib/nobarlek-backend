<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\TeamInjuryScrapperJob;
use App\Jobs\TeamScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\TeamScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class TeamInjuryScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:team-injury-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the team injuries data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new TeamInjuryScrapperJob());
    }
}
