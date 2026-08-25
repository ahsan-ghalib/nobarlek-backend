<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CoachScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use Illuminate\Console\Command;

class CoachScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:coach-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the coaches data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new CoachScrapperJob());
    }
}
