<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class HistoricalDataService
{
    private string $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFiles(): array
    {
        return Storage::disk('public')->files('/football-data/' . $this->fileName);
    }

    public function getJson($file): array
    {
        $jsonContents = \Illuminate\Support\Facades\Storage::disk('public')->get($file);
        return json_decode($jsonContents, true);
    }
}
