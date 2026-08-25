<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PlayerTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'from_team_id',
        'from_team_name',
        'to_team_id',
        'to_team_name',
        'transfer_type',
        'transfer_time',
        'transfer_fee',
        'transfer_desc',
    ];

    protected $appends = [
        'transfer_type_label',
    ];

    public function transferTime(): Attribute
    {
        return Attribute::get(
            fn ($value) => $value > 0 ? Carbon::createFromTimestamp((int) $value)->format('d M Y') : null
        );
    }

    public function transferTypeLabel(): Attribute
    {
        return Attribute::get(function ($value, array $attributes) {
            $type = (int) ($attributes['transfer_type'] ?? -1);
            $labels = \App\Enums\PlayerTransferTypeEnum::values();

            return $labels[$type] ?? 'Unknown';
        });
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function fromTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'from_team_id', 'team_id');
    }

    public function toTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'to_team_id', 'team_id');
    }
}
