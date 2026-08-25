<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum WeatherEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case CLOUDY = 'Cloudy';
    case PARTIALLY_CLOUDY_RAIN = 'Partially cloudy/rain';
    case SNOW = 'Snow';
    case SUNNY = 'Sunny';
    case OVERCAST_RAIN_PARTIAL_THUNDERSTORM = 'Overcast Rain/partial thunderstorm';
    case OVERCAST = 'overcast';
    case MIST = 'mist';
    case CLOUDY_WITH_RAIN = 'cloudy with rain';
    case CLOUDY_WITH_RAIN_PARTIAL_THUNDERSTORMS = 'cloudy with rain/partial Thunderstorms';
    case CLOUDS_RAINS_AND_THUNDERSTORMS_LOCALLY = 'Clouds/rains and thunderstorms locally';
    case FOG = 'Fog';
}
