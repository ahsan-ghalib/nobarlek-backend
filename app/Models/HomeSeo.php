<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HomeSeo extends Model
{
    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_level',
    ];

    public function metaKeywords(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? explode(', ', $value) : [],
            set: fn ($value) => is_array($value) ? implode(', ', $value) : $value
        );
    }
}

