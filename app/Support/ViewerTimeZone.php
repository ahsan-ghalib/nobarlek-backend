<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Throwable;

final class ViewerTimeZone
{
    public static function fromRequest(?Request $request = null): string
    {
        $request ??= request();
        foreach ([$request->input('timezone'), $request->input('tz')] as $candidate) {
            $resolved = self::sanitize($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return (string) config('app.timezone', 'UTC');
    }

    public static function sanitize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $timezone = trim($value);
        if ($timezone === '' || strlen($timezone) > 64) {
            return null;
        }

        try {
            new DateTimeZone($timezone);

            return $timezone;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * UTC unix range covering one calendar day in the viewer timezone.
     *
     * @return array{0: int, 1: int}
     */
    public static function dayWindow(?string $date, ?string $timezone = null): array
    {
        $timezone ??= self::fromRequest();
        $day = $date
            ? Carbon::parse($date, $timezone)
            : Carbon::now($timezone);

        return [
            $day->copy()->startOfDay()->timestamp,
            $day->copy()->endOfDay()->timestamp,
        ];
    }
}
