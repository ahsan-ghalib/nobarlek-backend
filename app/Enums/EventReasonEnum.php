<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum EventReasonEnum: string
{
    use ArrayableEnum;

    case UNKNOWN = 'Unknown';
    case FOUL = 'Foul';
    case PROFESSIONAL_FOUL = 'Professional foul';
    case ENCROACHMENT_INJURY_SUBSTITUTION = 'Encroachment/Injury substitution';
    case TACTICAL_FOUL_TACTICAL_SUBSTITUTION = 'Tactical Foul/Tactical substitution';
    case RECKLESS_OFFENCE = 'Reckless Offence';
    case OFF_THE_BALL_FOUL = 'Off the ball foul';
    case PERSISTENT_FOULING = 'Persistent fouling';
    case PERSISTENT_INFRINGEMENT = 'Persistent Infringement';
    case VIOLENT_CONDUCT = 'Violent conduct';
    case DANGEROUS_PLAY = 'Dangerous play';
    case HANDBALL = 'Handball';
    case SERIOUS_FOUL = 'Serious Foul';
    case PROFESSIONAL_FOUL_LAST_MAN = 'Professional foul last man';
    case DENIED_GOAL_SCORING_OPPORTUNITY = 'Denied goal-scoring opportunity';
    case TIME_WASTING = 'Time wasting';
    case VIDEO_SYNC_DONE = 'Video sync done';
    case RESCINDED_CARD = 'Rescinded Card';
    case ARGUMENT = 'Argument';
    case DISSENT = 'Dissent';
    case FOUL_AND_ABUSIVE_LANGUAGE = 'Foul and Abusive Language';
    case EXCESSIVE_CELEBRATION = 'Excessive celebration';
    case NOT_RETREATING = 'Not Retreating';
    case FIGHT = 'Fight';
    case EXTRA_FLAG_TO_CHECKER = 'Extra flag to checker';
    case ON_BENCH = 'On bench';
    case POST_MATCH = 'Post match';
    case OTHER_REASON = 'Other reason';
    case UNALLOWED_FIELD_ENTERING = 'Unallowed field entering';
    case ENTERING_FIELD = 'Entering field';
    case LEAVING_FIELD = 'Leaving field';
    case UNSPORTING_BEHAVIOUR = 'Unsporting behaviour';
    case NOT_VISIBLE = 'Not visible';
    case FLOP = 'Flop';
    case EXCESSIVE_USAGE_OF_REVIEW_SIGNAL = 'Excessive usage of review signal';
    case ENTERING_REFEREE_REVIEW_AREA = 'Entering referee review area';
    case SPITTING = 'Spitting';
    case VIRAL = 'Viral';
}
