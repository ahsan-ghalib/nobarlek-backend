<?php

namespace Database\Seeders\HistoricalData;

use App\Models\FootballMatch;
use App\Models\MatchLineUp;
use App\Services\HistoricalDataService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MatchPlayerLineupSeeder extends Seeder
{
    private array $realtimeData;
    private string $matchId;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->callInterface('match-lineup-july-2022');
        $this->callInterface('match-lineup-sept-2022');
        $this->callInterface('match-lineup-jan-2023');
        $this->callInterface('match-lineup-august-2023');
    }

    public function callInterface(string $folderName): void
    {
        $historicalDataService = new HistoricalDataService($folderName);
        $files = $historicalDataService->getFiles();

        foreach ($files as $file) {
            $data = $historicalDataService->getJson($file);
            $pathInfo = pathinfo($file);
            $this->matchId = $pathInfo['filename'];
            if (! ScrapingCountryScope::matchIdAllowed($this->matchId)) {
                continue;
            }
            $this->realtimeData = $data;

            $this->insertData();
        }
    }

    public function insertData(): void
    {
        FootballMatch::query()
            ->where('match_id', '=', $this->matchId)
            ->update([
                'away_formation' => $this->realtimeData['results']['away_formation'] ?? '-',
                'home_formation' => $this->realtimeData['results']['home_formation'] ?? '-',
                'away_coach_id' => $this->realtimeData['results']['coach_id']['away'] ?? '-',
                'home_coach_id' => $this->realtimeData['results']['coach_id']['home'] ?? '-',
                'confirmed' => $this->realtimeData['results']['confirmed'] ?? 0,
            ]);

        MatchLineUp::query()
            ->upsert($this->mergePlayerData('lineup'), ['match_id', 'player_id']);
    }

    public function mergePlayerData(string $type): array
    {
        return $this->addMatchIdAndType($type, 'home')
            ->merge($this->addMatchIdAndType($type, 'away'))
            ->toArray();
    }

    public function addMatchIdAndType(string $type, string $subType): Collection
    {
        return collect($this->realtimeData['results'][$type][$subType])
            ->transform(function ($injury) use ($subType) {
                $injury = array_merge($injury, ['type' => $subType, 'match_id' => $this->matchId]);
                $injury['player_id'] = $injury['id'];
                unset($injury['id']);
                unset($injury['incidents']);
                return $injury;
            });
    }
}
