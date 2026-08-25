<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum DataUpdatesTypeEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case SINGLE_MATCH_LINEUP = 'single match lineup';
    case BRACKET = 'bracket';
    case SEASON_STANDING = 'season standing';
    case SEASON_TEAM_STATISTICS = 'season team statistics';
    case SEASON_PLAYER_STATISTICS = 'season player statistics';
    case SEASON_TOP_SCORER = 'season top scorer';
    case FIFA_MEN = 'fifa men';
    case FIFA_WOMEN = 'fifa women';
    case WORLD_CLUBS_RANKING = 'world clubs ranking';
}
