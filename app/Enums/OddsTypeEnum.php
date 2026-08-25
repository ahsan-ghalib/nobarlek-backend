<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum OddsTypeEnum: string
{
    use ArrayableEnum;

    case  Asia_Handicap = 'asia';
    case  X_1_2 = 'eu';
    case  Total_Goals = 'bs';
    case  Corner_Kicks = 'cr';
}
