<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\RealtimeMatchDataScrapperJob;
use App\Jobs\VenueScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\RealtimeMatchDataScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class RealtimeMatchDataScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:real-time-match-data-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the real-time match data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new RealtimeMatchDataScrapperJob());
    }
}
