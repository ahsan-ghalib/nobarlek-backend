<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\MatchChartStatisticScrapperJob;

use Illuminate\Console\Command;

class MatchChartStatisticScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:match-chart-statistics-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the match chart statistics data from the api resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new MatchChartStatisticScrapperJob());
    }
}
