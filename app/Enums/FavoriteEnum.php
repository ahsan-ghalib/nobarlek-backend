<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum FavoriteEnum: string
{
    use ArrayableEnum;

    case TEAMS = 'teams';
    case PLAYERS = 'players';
    case LEAGUES = 'leagues';
    case MATCHES = 'matches';
}
