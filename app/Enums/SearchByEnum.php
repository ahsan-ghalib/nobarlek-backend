<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum SearchByEnum: string
{
    use ArrayableEnum;
    case All = 'All';
    case Teams = 'Teams';
    case Leagues = 'Leagues';
    case Players = 'Players';
}
