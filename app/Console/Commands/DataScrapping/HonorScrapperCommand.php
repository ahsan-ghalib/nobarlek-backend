<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\HonorScrapperJob;
use Illuminate\Console\Command;

class HonorScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:honor-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the honors data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new HonorScrapperJob());
    }
}
