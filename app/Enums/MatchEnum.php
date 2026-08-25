<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum MatchEnum: string
{
    use ArrayableEnum;
    case Football = 'Football';
}
