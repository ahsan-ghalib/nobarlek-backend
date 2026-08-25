<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\TeamSquadScrapperJob;
use App\Services\DataScrapping\TeamSquadScrapingService;
use Illuminate\Console\Command;

class TeamSquadScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:team-squad-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the teams data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new TeamSquadScrapperJob());
    }
}
