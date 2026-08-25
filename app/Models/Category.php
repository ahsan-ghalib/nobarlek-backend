<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'category_id', 'category_id');
    }
}
