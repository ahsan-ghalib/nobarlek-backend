<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CountryScrapperJob;
use App\Services\DataScrapping\CountryScrapingService;
use Illuminate\Console\Command;

class CountryScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:country-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the countries data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new CountryScrapperJob());
    }
}
