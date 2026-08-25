<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CategoryScrapperJob;
use App\Services\DataScrapping\CategoryScrapingService;
use Illuminate\Console\Command;

class CategoryScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:category-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the categories data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new CategoryScrapperJob());
    }
}
