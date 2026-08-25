<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum IncidentResultEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case GOAL_CONFIRMED = 'Goal confirmed';
    case GOAL_CANCELLED = 'Goal cancelled';
    case PENALTY_CONFIRMED = 'Penalty confirmed';
    case PENALTY_CANCELLED = 'Penalty cancelled';
    case RED_CARD_CONFIRMED = 'Red card confirmed';
    case RED_CARD_CANCELLED = 'Red card cancelled';
    case CARD_UPGRADE_CONFIRMED = 'Card upgrade confirmed';
    case CARD_UPGRADE_CANCELLED = 'Card upgrade cancelled';
    case ORIGINAL_DECISION = 'Original decision';
    case ORIGINAL_DECISION_CHANGED = 'Original decision changed';
}
