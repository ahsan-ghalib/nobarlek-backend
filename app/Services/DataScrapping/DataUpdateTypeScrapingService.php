<?php

namespace App\Services\DataScrapping;

use App\Enums\DataUpdatesTypeEnum;
use App\Models\FootballData;
use App\Models\FootballMatch;
use App\Support\ScrapingCountryScope;
use App\Traits\FootballScoreApiTrait;

class DataUpdateTypeScrapingService
{
    use FootballScoreApiTrait;

    private array $dataUpdateTypesEnum;

    public function __construct()
    {
        $this->dataUpdateTypesEnum = DataUpdatesTypeEnum::values();
    }

    /**
     * Scrap and inset/update the Categories from the sports api.
     */
    public function scrapDataUpdateTypes(array $params = []): void
    {
        $payload = $this->callApi('/football/data/update', $params);

        try {
            if (($payload['code'] ?? -1) !== 0 || empty($payload['results']) || ! is_array($payload['results'])) {
                return;
            }

            $matchIds = collect($payload['results'])
                ->filter(fn ($dataType) => is_array($dataType))
                ->flatMap(fn ($dataType) => collect($dataType)->pluck('match_id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $compByMatch = collect();
            if (ScrapingCountryScope::shouldApply() && $matchIds !== []) {
                $compByMatch = FootballMatch::query()->whereIn('match_id', $matchIds)->pluck('competition_id', 'match_id');
            }

            foreach ($payload['results'] as $key => $dataType) {
                if (! is_array($dataType)) {
                    continue;
                }
                $dataTypeLabel = $this->dataUpdateTypesEnum[(int) $key]
                    ?? sprintf('data update type %d', (int) $key);

                $dataUpdate = collect($dataType)
                    ->filter(function ($data) use ($compByMatch) {
                        if (! is_array($data)) {
                            return false;
                        }
                        if (! ScrapingCountryScope::shouldApply()) {
                            return true;
                        }
                        $mid = (string) ($data['match_id'] ?? '');
                        if ($mid === '') {
                            return false;
                        }
                        $cid = $compByMatch[$mid] ?? null;

                        return ScrapingCountryScope::competitionIdAllowed($cid !== null ? (string) $cid : null);
                    })
                    ->map(function ($data) use ($dataTypeLabel) {
                        return [
                            'data_type' => $dataTypeLabel,
                            'match_id' => $data['match_id'] ?? '',
                            'season_id' => $data['season_id'] ?? '',
                            'update_time' => $data['update_time'] ?? '',
                            'pub_time' => $data['pub_time'] ?? 0,
                            'updated_at' => now(),
                        ];
                    });

                if ($dataUpdate->isEmpty()) {
                    continue;
                }

                FootballData::query()
                    ->upsert($dataUpdate->toArray(), ['match_id', 'season_id', 'data_type']);
            }

        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/data/update: '.$exception->getMessage());
        }

    }
}
