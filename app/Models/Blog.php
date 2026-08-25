<?php

namespace App\Models;

use App\Helpers\SnakeCaseHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\GetImageAbsolutePathTrait;

class Blog extends Model
{
    use HasFactory, SoftDeletes, GetImageAbsolutePathTrait;

    protected $fillable = [
        'football_match_id',
        'slug',
        'title',
        'team_description',
        'footer_description',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'read_counts',
        'is_top',
        'is_published',
        'published_at',
    ];

    protected static function booted()
    {
        static::creating(function (Blog $blog) {
            $blog->slug = SnakeCaseHelper::toSnakeCase($blog->title);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->diffForHumans()
        );
    }

    public function publishedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ?  Carbon::parse($value)->diffForHumans() : ''
        );
    }
    public function footballMatch(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class);
    }

    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getAbsoluteUrl($value),
        );
    }

}
