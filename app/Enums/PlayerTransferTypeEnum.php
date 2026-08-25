<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum PlayerTransferTypeEnum: string
{
    use ArrayableEnum;

    case UNKNOWN_1 = 'Unknown_1';
    case LOAN = 'loan';
    case LOAN_END = 'loan end';
    case TRANSFER = 'transfer';
    case RETIREMENT = 'retirement';
    case DRAFT = 'draft';
    case RELEASED = 'released';
    case SIGNED = 'signed';
    case UNKNOWN = 'Unknown';

    /** API may send a numeric index or a string label — always coerce to DB integer index. */
    public static function indexFromApi(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw;
        }

        if (is_string($raw) && ctype_digit(trim($raw))) {
            return (int) trim($raw);
        }

        if (is_string($raw)) {
            $needle = strtolower(trim($raw));
            foreach (self::cases() as $index => $case) {
                if (strtolower($case->value) === $needle) {
                    return $index;
                }
            }
        }

        return 0;
    }
}
