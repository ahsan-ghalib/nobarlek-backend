<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\GetImageAbsolutePathTrait;

class Team extends Model
{
    use HasFactory, GetImageAbsolutePathTrait;

    protected $fillable = [
        'team_id',
        'competition_id',
        'country_id',
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
        'national',
        'country_logo',
        'foundation_time',
        'website',
        'coach_id',
        'venue_id',
        'market_value',
        'market_value_currency',
        'total_players',
        'foreign_players',
        'national_players',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_level',
        'about',
    ];

    protected $casts = [
        'is_image_saved' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'team_id';
    }

    public function logo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getImageAbsolutePath($value),
        );
    }

    public function metaKeywords(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? explode(', ', $value) : [],
            set: fn($value) => is_array($value) ? implode(', ', $value) : $value
        );
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'team_id', 'team_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'competition_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'coach_id', 'coach_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'home_team_id', 'team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'away_team_id', 'team_id');
    }

    public function squads(): HasMany
    {
        return $this->hasMany(TeamSquad::class, 'team_id', 'team_id');
    }

    public function squadPlayers(): BelongsToMany
    {
        // Players currently in squad via pivot table `team_squads` (team_id <-> player_id).
        return $this->belongsToMany(
            Player::class,
            'team_squads',
            'team_id',
            'player_id',
            'team_id',
            'player_id'
        );
    }

    public function teamStandings(): HasMany
    {
        return $this->hasMany(TeamStanding::class, 'team_id', 'team_id');
    }

    public function teamHonors(): HasMany
    {
        return $this->hasMany(TeamHonor::class, 'team_id', 'team_id');
    }

    public function matchTeamStatistics(): HasMany
    {
        return $this->hasMany(MatchTeamStatistic::class, 'team_id', 'team_id');
    }

    public function seasonTeamStatistics(): HasMany
    {
        return $this->hasMany(SeasonTeamStatistics::class, 'team_id', 'team_id');
    }

    public function topScorer(): HasMany
    {
        return $this->hasMany(SeasonTopScorer::class, 'team_id', 'team_id');
    }

    public function news(): MorphMany
    {
        return $this->morphMany(News::class, 'newsable');
    }

    public function countryLogo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getImageAbsolutePath($value),
        );
    }
}
