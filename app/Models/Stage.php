<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'season_id',
        'name',
        'mode',
        'group_count',
        'round_count',
        'order',
    ];

    public function getRouteKeyName(): string
    {
        return 'stage_od';
    }
}
