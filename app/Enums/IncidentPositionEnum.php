<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum IncidentPositionEnum: string
{
    use ArrayableEnum;

    case NEUTRAL = 'neutral';
    case HOME_TEAM = 'home team';
    case AWAY_TEAM = 'away team';
}
