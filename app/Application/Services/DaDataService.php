<?php

namespace App\Application\Services;

use App\Application\Models\DaDataCache;
use Illuminate\Support\Facades\Http;

class DaDataService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.dadata.token', '');
    }

    /**
     * Поиск организации по ИНН
     */
    public function findByInn(string $inn): ?array
    {
        $hash = md5('party_'.$inn);
        $cached = DaDataCache::where('query_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if ($cached) {
            return json_decode($cached->response_json, true);
        }

        if (empty($this->token)) {
            return null;
        }

        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
            ->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party', [
                'query' => $inn,
            ]);

        if ($response->successful()) {
            $data = $response->json('suggestions.0');

            if ($data) {
                DaDataCache::create([
                    'query_hash' => $hash,
                    'response_json' => json_encode($data),
                    'expires_at' => now()->addDays(30),
                ]);
            }

            return $data;
        }

        return null;
    }
}
