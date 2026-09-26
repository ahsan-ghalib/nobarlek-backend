<?php

namespace App\Models;

use App\Traits\GetImageAbsolutePathTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedMatch extends Model
{
    use GetImageAbsolutePathTrait;

    public const TICKER_LIMIT = 4;

    protected $fillable = [
        'match_id',
        'banner',
        'ticker_match_ids',
        'is_active',
    ];

    protected $casts = [
        'ticker_match_ids' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'banner_url',
    ];

    protected $hidden = [
        'banner',
    ];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([], [
            'ticker_match_ids' => [],
            'is_active' => true,
        ]);
    }

    public function bannerUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->banner ? $this->getImageAbsolutePath($this->banner) : null,
        );
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }

    public function tickerMatchIds(): array
    {
        return array_values(array_filter($this->ticker_match_ids ?? []));
    }
}
