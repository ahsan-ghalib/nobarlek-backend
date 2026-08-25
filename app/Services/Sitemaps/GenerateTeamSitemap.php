<?php

namespace App\Services\Sitemaps;

use App\Models\Competition;
use App\Models\FootballSiteMap;
use App\Models\Team;
use App\Traits\ToSnakeCaseTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\Tags\Url;

class GenerateTeamSitemap
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

        Team::query()
            ->select(['id','team_id','name'])
            ->when(!$first, fn(Builder $query) => $query->whereBetween('created_at' , [now()->startOfMonth(), now()->endOfMonth()]))
            ->chunk(50000, function ($teams) use ($sitemap, $baseUrl) {
                foreach ($teams as $team) {
                    $sitemap->add(Url::create($baseUrl . 'team-' . $this->toSnakeCase($team->name) . '/' . $team->team_id)
                        ->setPriority(0.8)
                        ->setLastModificationDate(Carbon::yesterday())
                    );
                }

                $fileName = '/team-' . collect($teams)->first()->id . '-' . collect($teams)->last()->id . '-sitemap.xml';

                $sitemap->writeToFile(Storage::disk('public')->path($fileName));

                FootballSiteMap::query()
                    ->create([
                        'url' => $fileName,
                        'last_modified' => now(),
                    ]);
            });
    }
}
