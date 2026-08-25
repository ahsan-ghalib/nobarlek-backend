<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SaveFootballImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'football:save-images {--limit=100 : Maximum records to process for each type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download competition, team, and player images to local storage.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $targets = [
            [Competition::class, 'Competitions', 'football/competitions', 'competition_id'],
            [Team::class, 'Teams', 'football/teams', 'team_id'],
            [Player::class, 'Players', 'football/players', 'player_id'],
        ];

        foreach ($targets as [$modelClass, $label, $directory, $idColumn]) {
            [$saved, $skipped, $failed] = $this->processImages($modelClass, $directory, $idColumn, $limit);

            $this->line("{$label}: saved {$saved}, skipped {$skipped}, failed {$failed}.");
        }

        return self::SUCCESS;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function processImages(string $modelClass, string $directory, string $idColumn, int $limit): array
    {
        $saved = 0;
        $skipped = 0;
        $failed = 0;

        $modelClass::query()
            ->where('is_image_saved', false)
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->limit($limit)
            ->get()
            ->each(function (Model $model) use ($directory, $idColumn, &$saved, &$skipped, &$failed) {
                $source = (string) $model->getRawOriginal('logo');

                if ($this->isLocalPublicImage($source)) {
                    $model->forceFill(['is_image_saved' => true])->save();
                    $skipped++;

                    return;
                }

                if (! Str::startsWith($source, ['http://', 'https://'])) {
                    $skipped++;

                    return;
                }

                $path = $this->downloadImage($source, $directory, (string) ($model->{$idColumn} ?: $model->getKey()));

                if (! $path) {
                    $failed++;

                    return;
                }

                $model->forceFill([
                    'logo' => $path,
                    'is_image_saved' => true,
                ])->save();

                $saved++;
            });

        return [$saved, $skipped, $failed];
    }

    private function isLocalPublicImage(string $path): bool
    {
        if ($path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    private function downloadImage(string $source, string $directory, string $identifier): ?string
    {
        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders(['User-Agent' => 'UpLiga Image Downloader'])
                ->get($source);

            if (! $response->successful() || $response->body() === '') {
                return null;
            }

            $extension = $this->imageExtension($source, $response->header('Content-Type'));
            $filename = Str::slug($identifier) ?: (string) Str::uuid();
            $path = "{$directory}/{$filename}-".substr(sha1($source), 0, 12).".{$extension}";

            return Storage::disk('public')->put($path, $response->body()) ? $path : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function imageExtension(string $source, ?string $contentType): string
    {
        $extension = Str::lower(pathinfo((string) parse_url($source, PHP_URL_PATH), PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

        if (in_array($extension, $allowedExtensions, true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return match (Str::before((string) $contentType, ';')) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };
    }
}
