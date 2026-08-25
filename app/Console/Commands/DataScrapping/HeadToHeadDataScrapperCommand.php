<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\HeadToHeadDataDataScrapperJob;
use App\Jobs\RealtimeMatchDataScrapperJob;
use App\Jobs\VenueScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\HeadToHeadDataScrapingService;
use App\Services\DataScrapping\RealtimeMatchDataScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class HeadToHeadDataScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:head-to-head-data-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the head-to-head data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new HeadToHeadDataDataScrapperJob());
    }
}
