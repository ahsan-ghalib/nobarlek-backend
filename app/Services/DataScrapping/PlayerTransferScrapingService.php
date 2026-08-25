<?php

namespace App\Services\DataScrapping;

use App\Enums\PlayerTransferTypeEnum;
use App\Models\PlayerTransfer;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class PlayerTransferScrapingService
{
    use FootballScoreApiTrait;

    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    /**
     * Scrap and inset/update player transfers from the sports api.
     */
    public function scrapTeamPlayerData(array $params = []): array
    {
        $playerTransferData = $this->callApi('/football/player/transfer/list', $params);

        try {
            $allowedTeams = ScrapingCountryScope::shouldApply()
                ? array_flip(ScrapingCountryScope::allowedTeamIds())
                : [];

            $playerTransfer = collect($playerTransferData['results'] ?? [])
                ->map(function ($playerTransfer) use ($allowedTeams) {
                    return collect($playerTransfer['transfer'] ?? [])
                        ->filter(function ($transfer) use ($allowedTeams) {
                            if (! ScrapingCountryScope::shouldApply()) {
                                return true;
                            }
                            if ($allowedTeams === []) {
                                return false;
                            }
                            $f = $transfer['from_team_id'] ?? '';
                            $t = $transfer['to_team_id'] ?? '';

                            return isset($allowedTeams[$f]) || isset($allowedTeams[$t]);
                        })
                        ->map(fn ($transfer) => $this->normalizeTransferRow($transfer, (string) ($playerTransfer['id'] ?? '')));
                })
                ->flatten(1)
                ->filter()
                ->values()
                ->all();

            if ($playerTransfer !== []) {
                PlayerTransfer::query()
                    ->upsert($playerTransfer, ['player_id', 'from_team_id', 'to_team_id']);
            }

            return $playerTransferData['query'] ?? ['total' => 0];
        } catch (\Exception $exception) {
            $errorMessage = $exception->getMessage();
            info('/football/player/transfer/list: '.$errorMessage);

            return ['error' => $errorMessage, 'total' => 0];
        }
    }

    /**
     * @param  array<string, mixed>  $transfer
     * @return array<string, mixed>|null
     */
    private function normalizeTransferRow(array $transfer, string $playerId): ?array
    {
        if ($playerId === '') {
            return null;
        }

        return [
            'player_id' => $playerId,
            'from_team_id' => $transfer['from_team_id'] ?? null,
            'from_team_name' => (string) ($transfer['from_team_name'] ?? ''),
            'to_team_id' => $transfer['to_team_id'] ?? null,
            'to_team_name' => (string) ($transfer['to_team_name'] ?? ''),
            'transfer_type' => PlayerTransferTypeEnum::indexFromApi($transfer['transfer_type'] ?? 0),
            'transfer_time' => (int) ($transfer['transfer_time'] ?? 0),
            'transfer_fee' => (int) ($transfer['transfer_fee'] ?? 0),
            'transfer_desc' => $transfer['transfer_desc'] ?? null,
        ];
    }
}
