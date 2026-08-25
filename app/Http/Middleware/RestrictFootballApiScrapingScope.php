<?php

namespace App\Http\Middleware;

use App\Support\ScrapingCountryScope;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictFootballApiScrapingScope
{
    /**
     * After route model binding, reject Competition / Match / Team / Player / Season outside the scraping allow-list.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! ScrapingCountryScope::shouldApply()) {
            return $next($request);
        }

        $route = $request->route();
        if ($route === null) {
            return $next($request);
        }

        foreach (['competition', 'footballMatch', 'team', 'player', 'season'] as $param) {
            $value = $route->parameter($param);
            if (! $value instanceof Model) {
                continue;
            }
            if (! $this->allowed($param, $value)) {
                abort(404);
            }
        }

        return $next($request);
    }

    private function allowed(string $param, Model $model): bool
    {
        return match ($param) {
            'competition' => ScrapingCountryScope::competitionIdAllowed($model->getAttribute('competition_id')),
            'footballMatch' => ScrapingCountryScope::competitionIdAllowed($model->getAttribute('competition_id')),
            'team' => ScrapingCountryScope::teamIdAllowed($model->getAttribute('team_id')),
            'player' => ScrapingCountryScope::playerIdAllowed($model->getAttribute('player_id')),
            'season' => ScrapingCountryScope::seasonIdAllowed($model->getAttribute('season_id')),
            default => true,
        };
    }
}
