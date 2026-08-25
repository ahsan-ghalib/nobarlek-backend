<?php

namespace App\Support;

use App\Models\Competition;
use App\Models\Country;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ScrapingCountryScope
{
    private static bool $warnedEmptyCountries = false;

    public static function enabled(): bool
    {
        return (bool) config('scraping.indonesia_only', false);
    }

    /**
     * TheSports country_id strings to treat as in-scope (Indonesia by default when resolved from DB).
     */
    public static function resolvedCountryIds(): array
    {
        $fromConfig = config('scraping.allowed_country_ids', []);
        if ($fromConfig !== []) {
            return $fromConfig;
        }

        return Cache::remember('scraping_scope:country_ids:indonesia', 3600, function () {
            return Country::query()
                ->where(function ($q) {
                    foreach (['name'] as $col) {
                        $q->orWhere($col, 'like', '%Indonesia%');
                    }
                })
                ->pluck('country_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
        });
    }

    public static function shouldApply(): bool
    {
        if (! self::enabled()) {
            return false;
        }
        $countries = self::resolvedCountryIds();
        $extras = self::extraCompetitionIds();
        if ($countries === [] && $extras === []) {
            if (! self::$warnedEmptyCountries) {
                self::$warnedEmptyCountries = true;
                Log::warning(
                    'SCRAPING_INDONESIA_ONLY is enabled but no country ids and no SCRAPING_EXTRA_COMPETITION_IDS. Set SCRAPING_ALLOWED_COUNTRY_IDS or ensure a Country row for Indonesia exists.'
                );
            }

            return false;
        }

        return true;
    }

    public static function extraCompetitionIds(): array
    {
        return config('scraping.extra_competition_ids', []) ?: [];
    }

    /**
     * Competition UUIDs already stored locally: rows in {@see Competition} whose country_id
     * is in {@see resolvedCountryIds()}, plus {@see ScrapingCountryScope::extraCompetitionIds()}.
     */
    public static function allowedCompetitionIdsFromDb(): array
    {
        if (! self::shouldApply()) {
            return [];
        }

        return Cache::remember('scraping_scope:competition_ids', 60, function () {
            $countryIds = self::resolvedCountryIds();
            if ($countryIds === []) {
                return [];
            }

            return Competition::query()
                ->whereIn('country_id', $countryIds)
                ->pluck('competition_id')
                ->unique()
                ->filter()
                ->values()
                ->all();
        });
    }

    /** Merged allow-list for match / live pipelines. */
    public static function allowedCompetitionIdsMerged(): array
    {
        return array_values(array_unique(array_merge(
            self::allowedCompetitionIdsFromDb(),
            self::extraCompetitionIds(),
        )));
    }

    public static function competitionAllowedByApiCountry(?string $countryId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        if ($countryId === null || $countryId === '') {
            return false;
        }

        return in_array($countryId, self::resolvedCountryIds(), true);
    }

    /**
     * When ingesting competitions from the API, keep rows that belong to an allowed country.
     * Rows must still land in {@see Competition} before downstream scrapers use {@see competitionIdAllowed}.
     */
    public static function competitionAllowedFromApiRow(array $competition): bool
    {
        if (! self::shouldApply()) {
            return true;
        }

        return self::competitionAllowedByApiCountry($competition['country_id'] ?? null);
    }

    public static function competitionIdAllowed(?string $competitionId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        if ($competitionId === null || $competitionId === '') {
            return false;
        }
        $merged = self::allowedCompetitionIdsMerged();

        return $merged !== [] && in_array($competitionId, $merged, true);
    }

    public static function liveMatchPayloadAllowed(array $match): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        $merged = self::allowedCompetitionIdsMerged();
        if ($merged === []) {
            return false;
        }
        $cid = $match['competition_id'] ?? null;
        if ($cid !== null && $cid !== '' && in_array($cid, $merged, true)) {
            return true;
        }
        $mid = $match['id'] ?? $match['match_id'] ?? null;
        if ($mid === null || $mid === '') {
            return false;
        }
        $fromDb = FootballMatch::query()->where('match_id', $mid)->value('competition_id');

        return $fromDb !== null && in_array($fromDb, $merged, true);
    }

    public static function seasonIdAllowed(?string $seasonId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        if ($seasonId === null || $seasonId === '') {
            return false;
        }
        $cid = Season::query()->where('season_id', $seasonId)->value('competition_id');
        if ($cid === null) {
            return false;
        }

        return self::competitionIdAllowed($cid);
    }

    public static function allowedTeamIds(): array
    {
        if (! self::shouldApply()) {
            return [];
        }

        return Cache::remember('scraping_scope:team_ids', 60, function () {
            $compIds = self::allowedCompetitionIdsMerged();
            if ($compIds === []) {
                return [];
            }

            return Team::query()
                ->whereIn('competition_id', $compIds)
                ->pluck('team_id')
                ->unique()
                ->filter()
                ->values()
                ->all();
        });
    }

    public static function forgetCompetitionAndTeamCaches(): void
    {
        Cache::forget('scraping_scope:competition_ids');
        Cache::forget('scraping_scope:team_ids');
    }

    public static function teamIdAllowed(?string $teamId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        $ids = self::allowedTeamIds();

        return $teamId !== null && $teamId !== '' && $ids !== [] && in_array($teamId, $ids, true);
    }

    public static function playerIdAllowed(?string $playerId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        if ($playerId === null || $playerId === '') {
            return false;
        }
        $teamId = Player::query()->where('player_id', $playerId)->value('team_id');

        return $teamId !== null && self::teamIdAllowed($teamId);
    }

    /**
     * Narrow API / query builders to allowed competitions (merged ids). No-op when scope is off.
     */
    public static function restrictCompetitionsQuery(Builder $query, string $competitionIdColumn = 'competition_id'): Builder
    {
        if (! self::shouldApply()) {
            return $query;
        }
        $ids = self::allowedCompetitionIdsMerged();
        if ($ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($competitionIdColumn, $ids);
    }

    public static function restrictMatchesQuery(Builder $query, string $competitionIdColumn = 'competition_id'): Builder
    {
        return self::restrictCompetitionsQuery($query, $competitionIdColumn);
    }

    public static function restrictSeasonsQuery(Builder $query, string $competitionIdColumn = 'competition_id'): Builder
    {
        return self::restrictCompetitionsQuery($query, $competitionIdColumn);
    }

    /**
     * Historical JSON seeders: only process matches already stored for an in-scope competition.
     */
    public static function matchIdAllowed(?string $matchId): bool
    {
        if (! self::shouldApply()) {
            return true;
        }
        if ($matchId === null || $matchId === '') {
            return false;
        }

        $matchId = pathinfo($matchId, PATHINFO_FILENAME);
        $query = FootballMatch::query()->where('match_id', $matchId);
        self::restrictMatchesQuery($query);

        return $query->exists();
    }

    public static function restrictCountriesQuery(Builder $query): Builder
    {
        if (! self::shouldApply()) {
            return $query;
        }
        $ids = self::resolvedCountryIds();
        if ($ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('country_id', $ids);
    }

    public static function restrictTeamsQuery(Builder $query): Builder
    {
        if (! self::shouldApply()) {
            return $query;
        }
        $ids = self::allowedTeamIds();
        if ($ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('team_id', $ids);
    }

    public static function restrictPlayersQuery(Builder $query): Builder
    {
        if (! self::shouldApply()) {
            return $query;
        }
        $ids = self::allowedTeamIds();
        if ($ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('team_id', $ids);
    }
}
