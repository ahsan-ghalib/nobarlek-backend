<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\DashboardPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function isDashboardAdmin(): bool
    {
        return $this->hasRole(DashboardPermissions::adminRole());
    }

    public function dashboardPermissionNames(): array
    {
        if ($this->isDashboardAdmin()) {
            return DashboardPermissions::names();
        }

        return $this->getAllPermissions()->pluck('name')->values()->all();
    }

    public function toAuthArray(string $token): array
    {
        return array_merge($this->toArray(), [
            'token' => $token,
            'role' => $this->getRoleNames()->first() ?? '',
            'permissions' => $this->dashboardPermissionNames(),
        ]);
    }
}
