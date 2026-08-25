<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait FootballScoreApiTrait
{
    /**
     * Make a request to the sports API.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function callApi(string $endPoint, array $params = []): array
    {
        $apiUrl = rtrim((string) config('app.the_sports_api_url'), '/');

        $params = array_merge([
            'user' => config('app.the_sports_username'),
            'secret' => config('app.the_sports_secret'),
        ], $params);

        if ($apiUrl === '' || $params['user'] === '' || $params['secret'] === '') {
            Log::error('TheSports API credentials or base URL are not configured.');

            return [
                'code' => -1,
                'error' => 'TheSports API credentials or THE_SPORTS_API_URL are not configured.',
                'query' => ['total' => 0],
            ];
        }

        try {
            $response = Http::timeout(120)->get($apiUrl.$endPoint, $params);

            if ($response->ok()) {
                $json = $response->json();

                if (! is_array($json)) {
                    return [
                        'code' => -1,
                        'error' => 'TheSports API returned a non-JSON response.',
                        'query' => ['total' => 0],
                    ];
                }

                if (isset($json['err']) && is_string($json['err']) && $json['err'] !== '') {
                    $json['error'] = $json['err'];
                    $json['code'] = $json['code'] ?? -1;
                    $json['query'] = $json['query'] ?? ['total' => 0];
                }

                return $json;
            }

            Log::warning('TheSports API HTTP error', [
                'endpoint' => $endPoint,
                'status' => $response->status(),
                'body' => mb_substr((string) $response->body(), 0, 500),
            ]);

            return [
                'code' => $response->status(),
                'error' => 'TheSports API HTTP '.$response->status(),
                'query' => ['total' => 0],
            ];
        } catch (\Throwable $exception) {
            Log::error('TheSports API request failed', [
                'endpoint' => $endPoint,
                'message' => $exception->getMessage(),
            ]);

            return [
                'code' => -1,
                'error' => $exception->getMessage(),
                'query' => ['total' => 0],
            ];
        }
    }
}
