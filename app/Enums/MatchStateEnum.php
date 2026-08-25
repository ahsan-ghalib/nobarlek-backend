<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum MatchStateEnum: string
{
    use ArrayableEnum;

    case ABNORMAL = 'Abnormal(suggest hiding)';
    case NOT_STARTED = 'Not started';
    case FIRST_HALF = 'First half';
    case HALF_TIME = 'Half-time';
    case SECOND_HALF = 'Second half';
    case OVERTIME = 'Overtime';
    case OVERTIME_DEPRECATED = 'Overtime(deprecated)';
    case PENALTY_SHOOT_OUT = 'Penalty Shoot-out';
    case END = 'End';
    case DELAY = 'Delay';
    case INTERRUPT = 'Interrupt';
    case CUT_IN_HALF = 'Cut in half';
    case CANCEL = 'Cancel';
    case TO_BE_DETERMINED = 'To be determined';
}
