<?php

namespace App\Models;

use App\Traits\GetImageAbsolutePathTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Competition extends Model
{
    use HasFactory, GetImageAbsolutePathTrait;

    protected $fillable = [
        'competition_id',
        'category_id',
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
        'type',
        'cur_season_id',
        'cur_stage_id',
        'cur_round',
        'round_count',
        'title_holder',
        'most_titles',
        'newcomers',
        'divisions',
        'host',
        'primary_color',
        'secondary_color',
        'priority',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_level',
    ];

    protected $casts = [
        'is_image_saved' => 'boolean',
    ];

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

    /**
     * Order by `priority` (lower first); rows with null `priority` last, then `name`.
     */
    public function scopeOrderedBySequence(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN priority IS NULL THEN 1 ELSE 0 END')
            ->orderBy('priority')
            ->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'competition_id';
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'competition_id', 'competition_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'cur_stage_id', 'stage_id');
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class, 'competition_id', 'competition_id');
    }

    public function currentSeason(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'cur_season_id', 'season_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'cur_stage_id', 'stage_id');
    }

    public function currentStages(): HasMany
    {
        return $this->hasMany(Stage::class, 'season_id', 'cur_season_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'competition_id', 'competition_id');
    }

    public function teamHonors(): HasMany
    {
        return $this->hasMany(TeamHonor::class, 'competition_id', 'competition_id');
    }

    public function news(): MorphMany
    {
        return $this->morphMany(News::class, 'newsable');
    }
}
