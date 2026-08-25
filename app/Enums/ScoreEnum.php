<?php

namespace App\Enums;

enum ScoreEnum: string
{
    case Score = '0';
    case Halftime_score = '1';
    case Red_cards = '2';
    case Yellow_cards = '3';
    case Corners = '4';
    case Overtime_score = '5';
    case Penalty = '6';
    case No_corner = '-1';
}
