<?php

namespace App\Models;

use App\Traits\GetImageAbsolutePathTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Player extends Model
{
    use HasFactory, GetImageAbsolutePathTrait;

    protected $fillable = [
        'player_id',
        'team_id',
        'name',
        'name_aa',
        'name_nl',
        'name_pt',
        'name_br',
        'name_es',
        'name_fr',
        'name_de',
        'name_vi',
        'short_name',
        'logo',
        'is_image_saved',
        'national_logo',
        'age',
        'birthday',
        'weight',
        'height',
        'country_id',
        'nationality',
        'market_value',
        'market_value_currency',
        'contract_until',
        'preferred_foot',
        'ability',
        'characteristics',
        'position',
        'positions',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_level',
        'about',
    ];

    protected $casts = [
        'ability' => 'array',
        'characteristics' => 'array',
        'positions' => 'array',
        'is_image_saved' => 'boolean',
    ];

    public function metaKeywords(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? explode(', ', $value) : [],
            set: fn($value) => is_array($value) ? implode(', ', $value) : $value
        );
    }

    public function getRouteKeyName(): string
    {
        return 'player_id';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function contractUntil(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }
                try {
                    $tz = request()->get('timezone') ?: config('app.timezone');
                    $ts = (int) $value;

                    return Carbon::createFromTimestamp($ts)->timezone($tz)->format('d M, Y');
                } catch (\Throwable) {
                    return null;
                }
            }
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function playerTransfers(): HasMany
    {
        return $this->hasMany(PlayerTransfer::class, 'player_id', 'player_id');
    }

    public function PlayerSalaries(): HasMany
    {
        return $this->hasMany(PlayerSalary::class, 'player_id', 'player_id');
    }

    public function PlayerSalary(): HasOne
    {
        return $this->hasOne(PlayerSalary::class, 'player_id', 'player_id')->latestOfMany();
    }

    public function playerHonors(): HasMany
    {
        return $this->hasMany(PlayerHonor::class, 'player_id', 'player_id');
    }

    public function playerLatestSquad(): HasOne
    {
        return $this->hasOne(TeamSquad::class, 'player_id', 'player_id')->latestOfMany();
    }

    public function seasonPlayerStatistics(): HasMany
    {
        return $this->hasMany(SeasonPlayerStatistics::class, 'player_id', 'player_id');
    }

    public function playerMatchStatistics(): HasOne
    {
        return $this->hasOne(MatchPlayerStatistic::class, 'player_id', 'player_id');
    }

    public function news(): MorphMany
    {
        return $this->morphMany(News::class, 'newsable');
    }

    public function logo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getImageAbsolutePath($value),
        );
    }

    public function nationalLogo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getImageAbsolutePath($value),
        );
    }
}
