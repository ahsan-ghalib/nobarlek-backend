<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stream playback URL template
    |--------------------------------------------------------------------------
    |
    | TheSports returns RTMP push URLs. Browsers need HLS (or similar) playback.
    | After TheSports restreams into your CDN, map the RTMP stream key into a
    | playback URL with this template. Use {stream_key} as the placeholder.
    |
    | Example: https://cdn.example.com/live/{stream_key}.m3u8
    |
    */
    'playback_url_template' => env('STREAM_PLAYBACK_URL_TEMPLATE', ''),

    /*
    |--------------------------------------------------------------------------
    | Football-only filter
    |--------------------------------------------------------------------------
    |
    | TheSports video list includes sport_id 1 (football) and 2 (basketball).
    | MVP stores football only.
    |
    */
    'football_sport_id' => (int) env('STREAM_FOOTBALL_SPORT_ID', 1),

];
