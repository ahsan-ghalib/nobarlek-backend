<?php

namespace App\Http\Resources;

use App\Models\Competition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->favoriteable_type === Team::class ? 'teams' : ($this->favoriteable_type === Player::class ? 'players' : ($this->favoriteable_type === Competition::class ?'leagues' : 'matchES')),
            'id' => $this->favoriteable_id,
        ];
    }
}
