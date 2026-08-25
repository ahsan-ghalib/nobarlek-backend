<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\CoachHonorScrapperJob;
use Illuminate\Console\Command;

class CoachHonorScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:coach-honor-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the coach honors data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new CoachHonorScrapperJob());
    }
}
