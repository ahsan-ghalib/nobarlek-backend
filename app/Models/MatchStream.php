<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'sport_id',
        'match_time',
        'synced_at',
    ];

    protected $casts = [
        'sport_id' => 'integer',
        'match_time' => 'integer',
        'synced_at' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'has_stream',
    ];

    public function footballMatch(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }

    /**
     * Presence of a row means TheSports lists a video signal for this match.
     */
    public function hasStream(): Attribute
    {
        return Attribute::make(
            get: fn () => true,
        );
    }
}
