<?php

namespace Database\Seeders;

use App\Services\DataScrapping\PlayerScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new PlayerScrapingService())->scrapPlayerData(['page' => $pageNo]);
            $pageNo++;
        } while ($query['total'] !== 0);
    }
}
