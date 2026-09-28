<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait GetImageAbsolutePathTrait
{
    /**
     * Remote URLs pass through; stored paths become public-disk URLs. Empty values
     * return null so clients show their placeholder instead of a broken image.
     */
    public function getImageAbsolutePath(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        $url = Storage::disk('public')->url(ltrim($value, '/'));

        // Without APP_URL the disk URL is relative; resolve it against the current host.
        return Str::startsWith($url, ['http://', 'https://', '//']) ? $url : url($url);
    }
}
