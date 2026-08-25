<?php

namespace App\Console\Commands\DataScrapping;

use App\Enums\DataUpdatesTypeEnum;
use App\Jobs\TeamStandingScrapperJob;
use App\Models\FootballData;
use App\Models\Season;
use Illuminate\Console\Command;

class TeamStandingScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:team-standings-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the team standings data from the api resource';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new TeamStandingScrapperJob());
    }
}
