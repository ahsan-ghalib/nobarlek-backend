<?php

namespace App\Models;

use App\Helpers\SnakeCaseHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\GetImageAbsolutePathTrait;

class News extends Model
{
    use HasFactory, SoftDeletes, GetImageAbsolutePathTrait;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'read_counts',
        'is_top',
        'is_published',
        'published_at',
        'newsable_id',
        'newsable_type',
    ];

    protected $casts = [
        'is_top' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function (News $news) {
            $news->slug = SnakeCaseHelper::toSnakeCase($news->title);
        });
    }

    public function newsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function metaKeywords(): Attribute
    {
        return Attribute::make(
            get: fn($value) => explode(', ', $value),
            set: fn($value) => implode(', ', $value)
        );
    }

    public function scopeIsPublished($query)
    {
        return $query->where('is_published', '=', true);
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

    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getImageAbsolutePath($value),
        );
    }
}
