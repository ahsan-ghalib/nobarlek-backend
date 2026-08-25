<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\PlayerTransferScrapperJob;

use App\Services\DataScrapping\PlayerTransferScrapingService;
use Illuminate\Console\Command;

class PlayerTransferScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:player-transfer-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the players transfer data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new PlayerTransferScrapperJob());
    }
}
