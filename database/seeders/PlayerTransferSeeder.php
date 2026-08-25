<?php

namespace Database\Seeders;

use App\Services\DataScrapping\PlayerTransferScrapingService;
use Illuminate\Database\Seeder;

class PlayerTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;

        do {
            $query = (new PlayerTransferScrapingService())->scrapTeamPlayerData(['page' => $pageNo]);

            if (isset($query['error'])) {
                $this->command?->error('Player transfer scrape failed: '.$query['error']);

                return;
            }

            $pageNo++;
        } while (($query['total'] ?? 0) > 0);
    }
}
