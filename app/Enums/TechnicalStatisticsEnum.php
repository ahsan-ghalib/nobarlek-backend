<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum TechnicalStatisticsEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case GOAL = 'Goal';
    case CORNER = 'Corner';
    case YELLOW_CARD = 'Yellow card';
    case RED_CARD = 'Red card';
    case OFFSIDE = 'Offside';
    case FREE_KICK = 'Free kick';
    case GOAL_KICK = 'Goal kick';
    case PENALTY = 'Penalty';
    case SUBSTITUTION = 'Substitution';
    case START = 'Start';
    case MIDFIELD = 'Midfield';
    case END = 'End';
    case HALFTIME_SCORE = 'Halftime score';
    case No_DATA_1 = 'no data 1';
    case CARD_UPGRADE_CONFIRMED = 'Card upgrade confirmed';
    case PENALTY_MISSED = 'Penalty missed';
    case OWN_GOAL = 'Own goal';
    case No_DATA_2 = 'no data 2';
    case INJURY_TIME = 'Injury time';
    case No_DATA_3 = 'no data 3';
    case SHOTS_ON_TARGET = 'Shots on target';
    case SHOTS_OFF_TARGET = 'Shots off target';
    case ATTACKS = 'Attacks';
    case DANGEROUS_ATTACK = 'Dangerous Attack';
    case BALL_POSSESSION = 'Ball possession';
    case OVERTIME_IS_OVER = 'Overtime is over';
    case PENALTY_KICK_ENDED = 'Penalty kick ended';
    case VAR_VIDEO_ASSISTANT_REFEREE = 'VAR(Video assistant referee)';
    case PENALTY_PENALTY_SHOOT_OUT = 'Penalty(Penalty Shoot-out)';
    case PENALTY_MISSED_PENALTY_SHOOT_OUT = 'Penalty missed(Penalty Shoot-out)';
}
