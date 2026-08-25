<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'capacity',
        'country_id',
        'city',
        'country',
    ];

    public function getRouteKeyName(): string
    {
        return 'venue_id';
    }
}
