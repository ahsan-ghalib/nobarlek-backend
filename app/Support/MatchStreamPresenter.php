<?php

namespace App\Support;

use App\Models\FootballMatch;

class MatchStreamPresenter
{
    public static function attach(FootballMatch $match): FootballMatch
    {
        $stream = $match->relationLoaded('stream') ? $match->stream : $match->stream()->first();

        $match->setAttribute('has_stream', filled($stream?->playback_url));
        $match->setAttribute('playback_url', $stream?->playback_url);
        $match->setAttribute('stream_quality', $stream?->stream_quality);

        return $match;
    }
}
