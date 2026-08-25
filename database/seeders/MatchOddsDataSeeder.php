<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\OddData;
use App\Traits\FootballScoreApiTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mockery\Exception;

class MatchOddsDataSeeder extends Seeder
{
    use FootballScoreApiTrait;

    public string $matchId;
    
    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FootballMatch::query()
            ->select('match_id')
            ->whereBetween('match_time', [1705847459, now()->timestamp])
            ->get()
            ->map(function ($match) {
            $this->matchId = $match->match_id;
                $oddsData = $this->callApi('/football/odds/history', ['uuid' => $match->match_id]);
                
                if ($oddsData['code'] === 0) {
                    $this->transformOddsData($oddsData['results']);
                } else {
                    info($match->match_id);
                }
            });
    }

    private function transformOddsData(array $oddsData)
    {
        collect($oddsData)->map(function ($odds, $companyId) {
            if (in_array($companyId, [2, 3, 4, 9])) {
                $oddsData = collect($odds)
                    ->map(function ($odds, $oddType) use ($companyId) {
                        return collect($odds)->map(function ($odd) use ($oddType, $companyId) {
                            return [
                                'company_id' => $companyId,
                                'match_id' => $this->matchId,
                                'type' => $oddType,
                                'change_time' => $odd[0],
                                'match_time' => $odd[1] ?: null,
                                'home_draw_away' => implode(',', [$odd[2], $odd[3], $odd[4], $odd[6]]),
                                'match_status' => $odd[5],
                                'goal_score' => $odd[7],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        });
                    })
                    ->flatten(1);

                OddData::query()
                    ->insert($oddsData->toArray());
            }
        });
    }
}
