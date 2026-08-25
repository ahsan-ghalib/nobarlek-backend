<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum CompanyEnum: string
{
    use ArrayableEnum;

    case  BET365 = 'BET365';
    case  Crown = 'Crown';
    case  BET_10 = '10BET';
    case  Ladbrokes = 'Ladbrokes';
    case  Mansion88 = 'Mansion88';
    case  Macauslot = 'Macauslot';
    case  SNAI = 'SNAI';
    case  William_Hill = 'William Hill';
    case  Easybets = 'Easybets';
    case  Vcbet = 'Vcbet';
    case  EuroBet = 'EuroBet';
    case  Interwetten = 'Interwetten';
    case  BET_12 = '12bet';
    case  Sbobet = 'Sbobet';
    case  Wewbet = 'Wewbet';
    case  Bet_18 = '18Bet';
    case  Fun88 = 'Fun88';
    case  bet_188 = '188bet';
    case  Pinnacle = 'Pinnacle';
}
