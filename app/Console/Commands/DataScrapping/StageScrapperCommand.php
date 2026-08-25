<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\StageScrapperJob;
use App\Services\DataScrapping\CoachScrapingService;
use App\Services\DataScrapping\StageScrapingService;
use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Console\Command;

class StageScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:stage-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the stages data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new StageScrapperJob());
    }
}
