<?php

namespace Database\Seeders\HistoricalData;

use App\Models\MatchPlayerStatistic;
use App\Services\HistoricalDataService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatchPlayerStatisticSeeder extends Seeder
{
    private array $realtimeData;
    private string $matchId;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->callInterface('match-player-stats-july-2022');
        $this->callInterface('match-player-stats-sept-2022');
        $this->callInterface('match-player-stats-jan-2023');
        $this->callInterface('match-player-stats-august-2023');
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
        collect($this->realtimeData['results'])
            ->map(fn($statistics) => array_merge($statistics, ['match_id' => $this->matchId]))
            ->chunk(100)
            ->map(fn($chunk) => MatchPlayerStatistic::query()
                ->upsert($chunk->toArray(), ['match_id', 'player_id', 'team_id'])
            );
    }
}
