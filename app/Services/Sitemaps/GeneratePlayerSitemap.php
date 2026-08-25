<?php

namespace App\Services\Sitemaps;

use App\Models\FootballSiteMap;
use App\Models\Player;
use App\Traits\ToSnakeCaseTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\SitemapIndex;
use Mfonte\Sitemap\Tags\Url;

class GeneratePlayerSitemap
{
    use ToSnakeCaseTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    public function generate($first = false): void
    {
        $sitemap = Sitemap::create();

        $baseUrl = config('app.frontend_url');

        Player::query()
            ->select(['id', 'player_id', 'name'])
            ->when(!$first, fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
            ->chunk(50000, function ($players) use ($sitemap, $baseUrl) {
                foreach ($players as $player) {
                    $sitemap->add(Url::create($baseUrl . 'player-' . $this->toSnakeCase($player->name) . '/' . $player->player_id)
                        ->setPriority(0.8)
                        ->setLastModificationDate(Carbon::yesterday())
                    );
                }

                $fileName = '/sitemaps/player-' . collect($players)->first()->id . '-' . collect($players)->last()->id . '-sitemap.xml';

                $sitemap->writeToFile(Storage::disk('public')->path($fileName));

                FootballSiteMap::query()
                    ->create([
                        'url' => $fileName,
                        'last_modified' => now(),
                    ]);
            });

        $sitemapIndex = SitemapIndex::create();
        $footballMatches = FootballSiteMap::query()
            ->get();

        foreach ($footballMatches as $footballMatch) {
            $sitemapIndex->add('/' . $footballMatch->url);
        }

        $sitemapIndex->writeToFile('sitemap.index');
    }
}
