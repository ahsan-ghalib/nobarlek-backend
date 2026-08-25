<?php

namespace Database\Seeders;

use App\Services\DataScrapping\HonorScrapingService;
use App\Services\DataScrapping\LanguageDataScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 5) as $type) {
            $pageNo = 1;
            do {
                $query = (new LanguageDataScrapingService())->scrapLanguageData(['page' => $pageNo, 'type' => $type]);
                $pageNo++;
            } while ($query['total'] !== 0);
        }
    }
}
