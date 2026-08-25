<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'category_id',
        'name',
        'name_aa',
        'name_nl',
        'name_pt',
        'name_br',
        'name_es',
        'name_fr',
        'name_de',
        'name_vi',
        'logo',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'country_id', 'country_id')->orderedBySequence();
    }
}
