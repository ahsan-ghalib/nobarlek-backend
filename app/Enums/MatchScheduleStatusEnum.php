<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum MatchScheduleStatusEnum: string
{
    use ArrayableEnum;
    case Result = 'Result';
    case Schedule = 'Schedule';
}
