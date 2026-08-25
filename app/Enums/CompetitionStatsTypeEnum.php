<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum CompetitionStatsTypeEnum: string
{
    use ArrayableEnum;

    case TEAM = 'team';
    case PLAYER = 'player';
}
