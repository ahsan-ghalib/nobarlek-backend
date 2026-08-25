<?php

namespace App\Console\Commands\DataScrapping;

use App\Jobs\MatchStreamScrapperJob;
use Illuminate\Console\Command;

class MatchStreamScrapperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-scrapping:match-stream-scrapper-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TheSports video push stream list (football) into match_streams';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        dispatch(new MatchStreamScrapperJob())->onQueue('high');

        $this->info('Match stream scrapper job dispatched.');

        return self::SUCCESS;
    }
}
