<?php

namespace Database\Seeders;

use App\Services\Sitemaps\GeneratePlayerSitemap;
use App\Services\Sitemaps\GenerateTeamSitemap;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerateTeamSiteMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new GenerateTeamSitemap())->generate(true);
    }
}
