<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\RefereeScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\RefereeScrapingService;
use Illuminate\Console\Command;

class RefereeScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:referee-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the referees data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new RefereeScrapperJob());
    }
}
