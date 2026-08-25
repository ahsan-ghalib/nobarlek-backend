<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'favoriteable_type',
        'favoriteable_id',
        'user_id',
    ];

        public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }
}
