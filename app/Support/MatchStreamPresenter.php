<?php

namespace App\Support;

use App\Models\FootballMatch;

class MatchStreamPresenter
{
    public static function attach(FootballMatch $match): FootballMatch
    {
        $stream = $match->relationLoaded('stream') ? $match->stream : $match->stream()->first();

        $match->setAttribute('has_stream', filled($stream));

        return $match;
    }
}
