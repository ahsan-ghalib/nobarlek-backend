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
        'pushurl1',
        'pushurl2',
        'playback_url',
        'synced_at',
    ];

    protected $casts = [
        'sport_id' => 'integer',
        'match_time' => 'integer',
        'synced_at' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'pushurl1',
        'pushurl2',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'stream_quality',
        'has_stream',
    ];

    public function footballMatch(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }

    public function streamQuality(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this->pushurl2) ? 'hd' : (filled($this->pushurl1) ? 'sd' : null),
        );
    }

    public function hasStream(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this->playback_url),
        );
    }

    /**
     * Prefer English HD (pushurl2), fall back to SD (pushurl1).
     */
    public function preferredPushUrl(): ?string
    {
        if (filled($this->pushurl2)) {
            return $this->pushurl2;
        }

        if (filled($this->pushurl1)) {
            return $this->pushurl1;
        }

        return null;
    }
}
