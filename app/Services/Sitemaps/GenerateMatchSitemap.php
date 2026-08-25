<?php

namespace App\Services\Sitemaps;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\FootballSiteMap;
use App\Models\Team;
use App\Traits\ToSnakeCaseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\SitemapIndex;
use Mfonte\Sitemap\Tags\Url;

class GenerateMatchSitemap
{
    use ToSnakeCaseTrait;

    public function generate($first = false): void
    {
        $sitemap = Sitemap::create();
        $baseUrl = config('app.frontend_url');

        FootballMatch::query()
            ->select(['football_matches.id', 'football_matches.match_id', 'home_team.name as home_team_name', 'away_team.name as away_team_name'])
            ->leftJoin('teams as home_team', 'football_matches.home_team_id', '=', 'home_team.team_id')
            ->leftJoin('teams as away_team', 'football_matches.away_team_id', '=', 'away_team.team_id')
            ->whereBetween('match_time', [now()->startOfDay()->timestamp, now()->endOfDay()->timestamp])
            ->get()
            ->map(function ($footballMatch) use ($sitemap, $baseUrl) {

                $sitemap->add(Url::create($baseUrl . '/match-' . $this->toSnakeCase($footballMatch->home_team_name) . '-vs-' . $this->toSnakeCase($footballMatch->away_team_name) . '/' . $footballMatch->match_id)
                    ->setPriority(0.9)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            });

        $fileName = '/sitemaps/match-' . now()->format('Y-m') . '-sitemap.xml';

        $sitemap->writeToFile(Storage::disk('public')->path($fileName));

        FootballSiteMap::query()
            ->updateOrCreate(['url' => $fileName],[
                'last_modified' => now(),
            ]);

        $sitemapIndex = SitemapIndex::create();
        $footballMatches = FootballSiteMap::query()
            ->get();

        foreach ($footballMatches as $footballMatch) {
            $sitemapIndex->add('https://api.footballscore.com/storage' . $footballMatch->url);
        }

        $sitemapIndex->writeToFile(Storage::disk('public')->path('sitemaps/sitemap.xml'));

    }
}
