<?php

namespace App\Services\DataScrapping;

use App\Models\FootballMatch;
use App\Models\MatchInjury;
use App\Models\MatchLineUp;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SingleMatchLineupDataScrapingService
{
    use FootballScoreApiTrait;

    protected array $params;

    protected array $realtimeData;

    /**
     * @param  array<string, mixed>  $params
     */
    public function scrapRealtimeMatchData(array $params = []): array
    {
        $this->params = $params;
        $matchId = (string) ($params['uuid'] ?? '');
        if (ScrapingCountryScope::shouldApply() && $matchId !== '') {
            $cid = FootballMatch::query()->where('match_id', $matchId)->value('competition_id');
            if (! ScrapingCountryScope::competitionIdAllowed($cid !== null ? (string) $cid : null)) {
                return ['error' => 'Match not in scraping scope'];
            }
        }

        $this->realtimeData = $this->callApi('/football/match/lineup/detail', $this->params);
        if (! is_array($this->realtimeData)) {
            return ['error' => 'Request failed'];
        }

        try {
            if (0 === ($this->realtimeData['code'] ?? -1) && is_array($this->realtimeData['results'] ?? null)
                && count($this->realtimeData['results']) > 0) {
                FootballMatch::query()
                    ->where('match_id', '=', $this->params['uuid'])
                    ->update([
                        'away_formation' => $this->realtimeData['results']['away_formation'] ?? '-',
                        'home_formation' => $this->realtimeData['results']['home_formation'] ?? '-',
                        'away_coach_id' => $this->realtimeData['results']['coach_id']['away'] ?? '-',
                        'home_coach_id' => $this->realtimeData['results']['coach_id']['home'] ?? '-',
                        'confirmed' => $this->realtimeData['results']['confirmed'] ?? 0,
                    ]);

                $this->syncPlayers(MatchLineUp::query(), $this->mergePlayerData('lineup'));
                $this->syncPlayers(MatchInjury::query(), $this->mergePlayerData('injury'));
            }

            return $this->realtimeData;
        } catch (\Exception $exception) {
            info('/football/match/lineup/detail: '.$exception->getMessage());

            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * Upsert the feed's rows and drop the match's rows the feed no longer lists.
     * The vendor sometimes swaps a player's id (e.g. a corrected spelling), and the
     * old row would otherwise stay and show as an extra starter.
     */
    private function syncPlayers(Builder $query, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        DB::transaction(function () use ($query, $rows) {
            (clone $query)->upsert($rows, ['match_id', 'player_id']);
            (clone $query)
                ->where('match_id', '=', $this->params['uuid'])
                ->whereNotIn('player_id', array_column($rows, 'player_id'))
                ->delete();
        });
    }

    public function mergePlayerData(string $type): array
    {
        return $this->addMatchIdAndType($type, 'home')
            ->merge($this->addMatchIdAndType($type, 'away'))
            ->toArray();
    }

    public function addMatchIdAndType(string $type, string $subType): Collection
    {
        $rows = $this->realtimeData['results'][$type][$subType] ?? [];

        return collect(is_array($rows) ? $rows : [])
            ->transform(function ($injury) use ($subType) {
                $injury = array_merge($injury, ['type' => $subType, 'match_id' => $this->params['uuid']]);
                $injury['player_id'] = $injury['id'];
                unset($injury['id']);
                unset($injury['incidents']);

                return $injury;
            });
    }
}
