<?php

namespace Database\Seeders;

use App\Services\DataScrapping\CoachHonorScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoachHonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new CoachHonorScrapingService())->scrapCoachHonorsData(['page'  => $pageNo]);
            sleep(5);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
