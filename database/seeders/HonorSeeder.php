<?php

namespace Database\Seeders;

use App\Services\DataScrapping\HonorScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new HonorScrapingService())->scrapHonorData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
