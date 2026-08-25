<?php

namespace Database\Seeders;

use App\Services\DataScrapping\CategoryScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new CategoryScrapingService())->scrapCategoryData();
    }
}
