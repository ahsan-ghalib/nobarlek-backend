<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CategoryScrapperJob;
use App\Jobs\DataUpdateTypeScrapperJob;
use App\Services\DataScrapping\CategoryScrapingService;
use Illuminate\Console\Command;

class DataUpdateTypeScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:data-update-types-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the data update types data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new DataUpdateTypeScrapperJob())->onQueue('high');
    }
}
