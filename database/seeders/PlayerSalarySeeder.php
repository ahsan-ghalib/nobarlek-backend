<?php

namespace Database\Seeders;

use App\Services\DataScrapping\PlayerSalariesScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new PlayerSalariesScrapingService())->scrapPlayerSalariesData(['page'  => $pageNo]);
            sleep(5);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
