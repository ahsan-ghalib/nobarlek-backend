<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\SeasonScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\RefereeScrapingService;
use App\Services\DataScrapping\SeasonScrapingService;
use Illuminate\Console\Command;

class SeasonScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:season-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the seasons data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new SeasonScrapperJob());
    }
}
