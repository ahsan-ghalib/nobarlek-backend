<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'team_id',
        'name',
        'logo',
        'age',
        'birthday',
        'preferred_formation',
        'country_id',
        'nationality',
    ];

    public function coachHonors(): HasMany
    {
        return $this->hasMany(CoachHonor::class, 'coach_id', 'coach_id');
    }
}
