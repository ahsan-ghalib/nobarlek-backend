<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\PlayerHonorScrapperJob;
use App\Jobs\TeamHonorScrapperJob;
use Illuminate\Console\Command;

class PlayerHonorScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:player-honor-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the player honors data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new PlayerHonorScrapperJob());
    }
}
