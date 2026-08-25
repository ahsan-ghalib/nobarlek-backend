<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchChartStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'count',
        'per',
        'timeline',
    ];

    protected $casts = [
        'timeline' => 'array',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_jd');
    }
}
