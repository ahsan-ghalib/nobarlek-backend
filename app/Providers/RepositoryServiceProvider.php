<?php

namespace App\Providers;

use App\Repositories\FootballMatchRepository;
use App\Repositories\Interfaces\FootballMatchInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FootballMatchRepository::class, FootballMatchInterface::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
