<?php

namespace App\Services\Sitemaps;

use App\Models\Competition;
use App\Models\FootballSiteMap;
use App\Traits\ToSnakeCaseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\Tags\Url;

class GenerateCompetitionSitemap
{
    use ToSnakeCaseTrait;

    public function generate(): void
    {
        $sitemap = Sitemap::create();
        $baseUrl = config('app.frontend_url');

        Competition::query()
            ->select(['competition_id', 'name'])
            ->get()
            ->map(function ($competition) use ($sitemap, $baseUrl) {
                $sitemap->add(Url::create($baseUrl . '/leagues-' . $this->toSnakeCase($competition->name) . '/' . $competition->competition_id)
                    ->setPriority(0.9)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            });

        $fileName = '/sitemaps/competition-sitemap.xml';

        $sitemap->writeToFile(Storage::disk('public')->path($fileName));

        FootballSiteMap::query()
            ->create([
                'url' => $fileName,
                'last_modified' => now(),
            ]);
    }
}
