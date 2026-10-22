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

                $this->syncPlayers(MatchLineUp::query(), $this->lineupRows());
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

    /**
     * Keep only columns the lineups table has. Extra vendor fields make the
     * upsert fail, and then every player for that match is dropped.
     *
     * @return array<int, array<string, mixed>>
     */
    private function lineupRows(): array
    {
        return array_values(array_filter(array_merge(
            $this->mapLineupSide('home'),
            $this->mapLineupSide('away'),
        )));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapLineupSide(string $side): array
    {
        $rows = $this->realtimeData['results']['lineup'][$side] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $playerId = trim((string) ($row['player_id'] ?? $row['id'] ?? ''));
            if ($playerId === '') {
                continue;
            }
            $out[] = [
                'type' => $side,
                'player_id' => $playerId,
                'match_id' => (string) $this->params['uuid'],
                'first' => $this->isStarter($row) ? 1 : 0,
                'captain' => ! empty($row['captain']) ? 1 : 0,
                'name' => (string) ($row['name'] ?? ''),
                'logo' => (string) ($row['logo'] ?? ''),
                'shirt_number' => (int) ($row['shirt_number'] ?? 0),
                'position' => (string) ($row['position'] ?? ''),
                'x' => (int) ($row['x'] ?? 0),
                'y' => (int) ($row['y'] ?? 0),
                'rating' => (string) ($row['rating'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isStarter(array $row): bool
    {
        $first = $row['first'] ?? null;
        if ($first === true || $first === 1 || $first === '1') {
            return true;
        }
        if ($first === false || $first === 0 || $first === '0') {
            return false;
        }
        // Some feeds use status: 0 starter, 1 substitute.
        if (isset($row['status']) && is_numeric($row['status'])) {
            return (int) $row['status'] === 0;
        }

        return ((int) ($row['x'] ?? 0) > 0) || ((int) ($row['y'] ?? 0) > 0);
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
