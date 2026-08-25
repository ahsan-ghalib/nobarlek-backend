<?php

namespace Database\Seeders;

use App\Services\Sitemaps\GenerateCompetitionSitemap;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerateCompititionScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new GenerateCompetitionSitemap())->generate(true);
    }
}
