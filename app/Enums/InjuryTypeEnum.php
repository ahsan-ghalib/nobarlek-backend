<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum InjuryTypeEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case INJURED = 'injured';
    case SUSPENDED = 'suspended';
}
