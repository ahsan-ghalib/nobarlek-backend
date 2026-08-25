<?php

namespace App\Support;

use Illuminate\Support\Collection;

class DashboardPermissions
{
    public static function adminRole(): string
    {
        return (string) config('dashboard-permissions.admin_role', 'admin');
    }

    public static function groups(): array
    {
        return config('dashboard-permissions.groups', []);
    }

    public static function names(): array
    {
        return self::nameCollection()->all();
    }

    public static function nameCollection(): Collection
    {
        return collect(self::groups())
            ->flatMap(fn (array $group) => collect($group['permissions'] ?? [])->pluck('name'))
            ->filter()
            ->values();
    }
}
