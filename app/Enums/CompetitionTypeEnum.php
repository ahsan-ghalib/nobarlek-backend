<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum CompetitionTypeEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case LEAGUE = 'league';
    case CUP = 'cup';
    case FRIENDLY = 'friendly';
}
