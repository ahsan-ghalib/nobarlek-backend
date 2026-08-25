<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('data-scrapping:match-scrapper-command')->everyMinute();
        // TheSports video push streams (~72h window); vendor recommends 1 min polling.
        $schedule->command('data-scrapping:match-stream-scrapper-command')->everyMinute();
        // TheSports diary: full day schedule/results (vendor: ~10 min today, ~30 min future days)
        $schedule->command('data-scrapping:match-diary-scrapper-command')->everyTenMinutes();
        $schedule->command('data-scrapping:match-diary-scrapper-command --future')->everyThirtyMinutes();
        $schedule->command('data-scrapping:real-time-match-data-scrapper-command')->everyThirtySeconds();
        $schedule->command('data-scrapping:match-chart-statistics-scrapper-command')->everyMinute();
        $schedule->command('data-scrapping:match-team-statistics-scrapper-command')->everyMinute();
        $schedule->command('data-scrapping:match-player-statistics-scrapper-command')->everyMinute();
        // $schedule->command('data-scrapping:odds-data-scrapper-command')->everyMinute();

        $schedule->command('data-scrapping:head-to-head-data-scrapper-command')->everyMinute(); // no need to add this command

        $schedule->command('data-scrapping:data-update-types-scrapper-command')->everyTwoMinutes();

        $schedule->command('data-scrapping:team-standings-scrapper-command')->everyFiveMinutes(); // run in every hour

        $schedule->command('data-scrapping:season-team-statistics-scrapper-command')->hourly();
        $schedule->command('data-scrapping:season-player-statistics-scrapper-command')->hourlyAt(9);
        $schedule->command('data-scrapping:single-match-lineups-scrapper-command')->hourlyAt(15);
        $schedule->command('data-scrapping:season-top-scorer-statistics-scrapper-command')->hourlyAt(17);
        $schedule->command('data-scrapping:stage-scrapper-command')->hourlyAt(19);

        $schedule->command('data-scrapping:competition-scrapper-command')->hourly();
        $schedule->command('data-scrapping:team-squad-scrapper-command')->hourly();
        $schedule->command('football:save-images')->hourlyAt(25)->withoutOverlapping();

        $schedule->command('data-scrapping:team-injury-scrapper-command')->everySixHours();

        $schedule->command('data-scrapping:player-transfer-scrapper-command')->dailyAt('1:00');
        $schedule->command('data-scrapping:team-scrapper-command')->dailyAt('2:07');
        $schedule->command('data-scrapping:player-scrapper-command')->dailyAt('3:07');
        $schedule->command('data-scrapping:coach-scrapper-command')->dailyAt('4:09');
        $schedule->command('data-scrapping:season-scrapper-command')->dailyAt('5:11');
        $schedule->command('data-scrapping:category-scrapper-command')->daily();
        $schedule->command('data-scrapping:country-scrapper-command')->daily();
        $schedule->command('data-scrapping:referee-scrapper-command')->daily();
        $schedule->command('data-scrapping:venue-scrapper-command')->daily();
        $schedule->command('data-scrapping:player-salaries-scrapper-command')->daily();
        $schedule->command('data-scrapping:honor-scrapper-command')->daily();
        $schedule->command('data-scrapping:team-honor-scrapper-command')->daily();
        $schedule->command('data-scrapping:player-honor-scrapper-command')->daily();
        $schedule->command('data-scrapping:coach-honor-scrapper-command')->daily();

        // $schedule->command('sitemap:generate-daily-matches-sitemap-command')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
