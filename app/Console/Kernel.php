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
        // Frequent scrapers run in the background so a slow one can't hold up the others
        // (schedule:run otherwise runs due commands one after another, delaying the
        // 30-second live feed), and without overlapping so slow runs don't pile up.
        $schedule->command('data-scrapping:match-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();
        // TheSports video push streams (~72h window); vendor recommends 1 min polling.
        $schedule->command('data-scrapping:match-stream-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();
        // TheSports diary: full day schedule/results (vendor: ~10 min today, ~30 min future days)
        $schedule->command('data-scrapping:match-diary-scrapper-command')->everyTenMinutes()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:match-diary-scrapper-command --future')->everyThirtyMinutes()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:real-time-match-data-scrapper-command')->everyThirtySeconds()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:match-chart-statistics-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:match-team-statistics-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:match-player-statistics-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();
        // $schedule->command('data-scrapping:odds-data-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground();

        $schedule->command('data-scrapping:head-to-head-data-scrapper-command')->everyMinute()->withoutOverlapping()->runInBackground(); // no need to add this command

        $schedule->command('data-scrapping:data-update-types-scrapper-command')->everyTwoMinutes()->withoutOverlapping()->runInBackground();

        $schedule->command('data-scrapping:team-standings-scrapper-command')->everyFiveMinutes()->withoutOverlapping()->runInBackground(); // run in every hour
        // table/live only covers tables that just changed; refresh every current season's
        // full table daily so leagues without recent live updates still have standings.
        $schedule->command('data-scrapping:historical-team-standings --current')->dailyAt('0:40')->withoutOverlapping();

        $schedule->command('data-scrapping:season-team-statistics-scrapper-command')->hourly();
        $schedule->command('data-scrapping:season-player-statistics-scrapper-command')->hourlyAt(9);
        // Lineups publish around kickoff; hourly was too slow for the live site.
        // One run handles both recently-updated and currently-live matches. Scheduling
        // the same command twice with withoutOverlapping could cause the --live run to
        // be skipped while the normal run held the command mutex.
        $schedule->command('data-scrapping:single-match-lineups-scrapper-command')->everyFiveMinutes()->withoutOverlapping()->runInBackground();
        $schedule->command('data-scrapping:season-top-scorer-statistics-scrapper-command')->hourlyAt(17);
        // The hourly jobs above only follow the last hour of update events; refresh the full
        // scorer and player tables for current seasons so every league has top scorers/assists.
        $schedule->command('data-scrapping:season-player-leaders')->dailyAt('1:30')->withoutOverlapping();
        $schedule->command('data-scrapping:stage-scrapper-command')->hourlyAt(19);

        $schedule->command('data-scrapping:competition-scrapper-command')->hourly();
        $schedule->command('data-scrapping:team-squad-scrapper-command')->hourly();
        $schedule->command('football:save-images')->hourlyAt(25)->withoutOverlapping();

        $schedule->command('data-scrapping:team-injury-scrapper-command')->everySixHours();

        $schedule->command('data-scrapping:player-transfer-scrapper-command')->dailyAt('1:00');
        $schedule->command('data-scrapping:team-scrapper-command')->dailyAt('2:07');
        $schedule->command('data-scrapping:missing-teams-scrapper-command')->hourlyAt(23)->withoutOverlapping();
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
