<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait GetImageAbsolutePathTrait
{
    public function getImageAbsolutePath(string $value): string
    {
        return Str::startsWith($value, 'http') ? $value : url(config('app.env') === 'production' ? 'backend/' . Storage::url($value) : Storage::url($value));
    }
}