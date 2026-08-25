<?php

namespace App\Console\Commands;

use App\Services\Sitemaps\GenerateMatchSitemap;
use Illuminate\Console\Command;

class GenerateDailyMatchesSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate-daily-matches-sitemap-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Daily Matches sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new GenerateMatchSitemap())->generate();
    }
}
