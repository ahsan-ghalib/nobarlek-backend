<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\MatchScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\MatchScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class MatchScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:match-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the matches data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new MatchScrapperJob())->onQueue('high');
    }
}
