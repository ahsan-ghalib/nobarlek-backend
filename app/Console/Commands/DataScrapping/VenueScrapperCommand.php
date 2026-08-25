<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\VenueScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class VenueScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:venue-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the venues data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new VenueScrapperJob());
    }
}
