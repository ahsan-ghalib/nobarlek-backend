<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Support\MatchTimeQuery;
use App\Support\ViewerTimeZone;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CompetitionFootballMatchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition, ?string $matchDate = null): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date:Y-m-d'],
            'match_date' => ['nullable', 'date:Y-m-d'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'tz' => ['nullable', 'string', 'max:64'],
        ]);

        $timezone = ViewerTimeZone::fromRequest($request);
        $requestedDate = $this->resolveRequestedDate($request, $matchDate);
        $dateFilter = $requestedDate !== null;

        if ($dateFilter) {
            $from = Carbon::parse($requestedDate, $timezone)->startOfDay()->timestamp;
            $to = Carbon::parse($requestedDate, $timezone)->endOfDay()->timestamp;
        } else {
            // Past month for recent results + next two months so upcoming fixtures appear on first load.
            $from = now($timezone)->copy()->subMonth()->startOfMonth()->timestamp;
            $to = now($timezone)->copy()->addMonths(2)->endOfMonth()->timestamp;
        }

        $matches = $this->matchesBetween(
            $competition,
            $from,
            $to,
            $dateFilter,
            $requestedDate,
            $timezone,
        );

        if (! $dateFilter && $matches->isEmpty()) {
            $nowUnix = now($timezone)->timestamp;
            $nextMatch = $competition->matches()
                ->whereRaw(MatchTimeQuery::unixExpression().' >= ?', [$nowUnix])
                ->orderByRaw(MatchTimeQuery::unixExpression().' ASC')
                ->first(['season_id', 'round_num', 'match_time', 'kickoff_time']);

            if ($nextMatch !== null) {
                $matches = $this->matchesForRound(
                    $competition,
                    $nextMatch->season_id,
                    $nextMatch->round_num,
                    $timezone,
                );
            } else {
                $latestMatch = $competition->matches()
                    ->whereRaw(MatchTimeQuery::unixExpression().' <= ?', [now($timezone)->endOfDay()->timestamp])
                    ->orderByRaw(MatchTimeQuery::unixExpression().' DESC')
                    ->first(['season_id', 'round_num', 'match_time', 'kickoff_time']);

                if ($latestMatch !== null) {
                    $matches = $this->matchesForRound(
                        $competition,
                        $latestMatch->season_id,
                        $latestMatch->round_num,
                        $timezone,
                    );
                }
            }
        }

        if ($dateFilter && $requestedDate !== null) {
            $matches = $matches->only([$requestedDate]);
        }

        return $this->matchScheduleResponse($matches, $dateFilter, $requestedDate, $from, $to, $timezone);
    }

    private function matchesBetween(
        Competition $competition,
        int $from,
        int $to,
        bool $dateFilter,
        ?string $requestedDate,
        string $timezone,
    ): Collection {
        return MatchTimeQuery::applyBetween(
            $competition->matches(),
            $from,
            $to,
            $dateFilter,
        )
            ->select([
                'match_id',
                'kickoff_time',
                'away_team_id',
                'home_team_id',
                'match_time',
                'status_id',
                'round_num',
                'home_scores',
                'away_scores',
            ])
            ->with([
                'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            ])
            ->orderByRaw(MatchTimeQuery::unixExpression($dateFilter).' ASC')
            ->get()
            ->filter(function (FootballMatch $match) use ($from, $to, $dateFilter, $requestedDate, $timezone) {
                $unix = MatchTimeQuery::effectiveUnix($match, $dateFilter);
                if ($unix < $from || $unix > $to) {
                    return false;
                }

                if ($dateFilter && $requestedDate !== null) {
                    return MatchTimeQuery::toCalendarDate($unix, $timezone) === $requestedDate;
                }

                return true;
            })
            ->values()
            ->map(function (FootballMatch $match) use ($dateFilter, $timezone) {
                $unix = MatchTimeQuery::effectiveUnix($match, $dateFilter);

                return array_merge($match->toArray(), [
                    'match_date' => MatchTimeQuery::toCalendarDate($unix, $timezone),
                ]);
            })
            ->groupBy(['match_date', 'round_num']);
    }

    private function matchesForRound(
        Competition $competition,
        string $seasonId,
        string $roundNumber,
        string $timezone,
    ): Collection {
        return $competition->matches()
            ->where('season_id', $seasonId)
            ->where('round_num', $roundNumber)
            ->select($this->matchColumns())
            ->with($this->matchRelations())
            ->orderByRaw(MatchTimeQuery::unixExpression().' ASC')
            ->get()
            ->map(function (FootballMatch $match) use ($timezone) {
                $unix = MatchTimeQuery::effectiveUnix($match);

                return array_merge($match->toArray(), [
                    'match_date' => MatchTimeQuery::toCalendarDate($unix, $timezone),
                ]);
            })
            ->groupBy(['match_date', 'round_num']);
    }

    private function matchColumns(): array
    {
        return [
            'match_id',
            'kickoff_time',
            'away_team_id',
            'home_team_id',
            'match_time',
            'status_id',
            'round_num',
            'home_scores',
            'away_scores',
        ];
    }

    private function matchRelations(): array
    {
        return [
            'homeTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
            'awayTeam:team_id,short_name,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
        ];
    }

    private function resolveRequestedDate(Request $request, ?string $routeDate): ?string
    {
        foreach ([
            $routeDate,
            $request->query('date'),
            $request->query('match_date'),
            $this->dateFromQueryString(),
            $this->dateFromRequestUri($request),
        ] as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    private function dateFromQueryString(): ?string
    {
        $raw = $_SERVER['QUERY_STRING'] ?? '';
        if ($raw === '') {
            return null;
        }

        parse_str($raw, $parsed);

        foreach (['date', 'match_date'] as $key) {
            $value = $parsed[$key] ?? null;
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value)) === 1) {
                return trim($value);
            }
        }

        return null;
    }

    private function dateFromRequestUri(Request $request): ?string
    {
        $uri = (string) ($request->server->get('REQUEST_URI') ?? '');
        if ($uri === '') {
            return null;
        }

        if (preg_match('/[?&](?:date|match_date)=(\d{4}-\d{2}-\d{2})/', $uri, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function matchScheduleResponse(
        Collection $matches,
        bool $dateFilter,
        ?string $requestedDate,
        int $from,
        int $to,
        string $timezone,
    ): JsonResponse {
        $payload = [
            'message' => 'Successfully retrieved data',
            'data' => $matches,
        ];

        if ($dateFilter) {
            $payload['meta'] = [
                'filter_date' => $requestedDate,
                'range_from' => Carbon::createFromTimestamp($from, $timezone)->toDateString(),
                'range_to' => Carbon::createFromTimestamp($to, $timezone)->toDateString(),
            ];
        }

        $response = response()->json($payload, Response::HTTP_OK);

        if ($dateFilter) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->set('X-Filter-Date', (string) $requestedDate);
        }

        return $response;
    }
}
