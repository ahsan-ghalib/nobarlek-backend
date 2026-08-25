<?php

namespace Database\Seeders;

use App\Services\DataScrapping\TeamHonorScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamHonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;

        do {
            $query = (new TeamHonorScrapingService())->scrapTeamHonorsData(['page' => $pageNo]);

            if (isset($query['error'])) {
                $this->command?->error($query['error']);
                break;
            }

            $pageNo++;
        } while (($query['total'] ?? 0) > 0);
    }
}
