<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\PlayerScrapperJob;
use App\Services\DataScrapping\PlayerScrapingService;
use Illuminate\Console\Command;

class PlayerScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:player-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the players data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new PlayerScrapperJob());
    }
}
