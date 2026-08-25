<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum IncidentReasonEnum: string
{
    use ArrayableEnum;

    case OTHER = 'Other';
    case GOAL_AWARDED = 'Goal awarded';
    case GOAL_NOT_AWARDED = 'Goal not awarded';
    case PENALTY_AWARDED = 'Penalty awarded';
    case PENALTY_NOT_AWARDED = 'Penalty not awarded';
    case RED_CARD_GIVEN = 'Red card given';
    case CARD_UPGRADE = 'Card upgrade';
    case MISTAKEN_IDENTITY = 'Mistaken identity';
}
