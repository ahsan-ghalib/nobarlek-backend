<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CategoryScrapperJob;
use App\Jobs\OddsScrapperJob;
use App\Services\DataScrapping\CategoryScrapingService;
use App\Services\DataScrapping\OddsDataScrapingService;
use Illuminate\Console\Command;

class OddsDataScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:odds-data-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the odds data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new OddsScrapperJob());
    }
}
