<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GSMArenaService
{
    /**
     * Поиск устройств через внешний API
     */
    public function searchDevice(string $query): array
    {
        // Кешируем запросы к API на 1 час, чтобы не забанили за лимиты
        return Cache::remember('api_device_search_' . Str::slug($query), 3600, function () use ($query) {
            try {
                // Используем бесплатный публичный API для поиска телефонов
                $response = Http::timeout(3)->get('https://phone-specs-api.azharimm.dev/search', [
                    'query' => $query
                ]);

                if ($response->successful() && isset($response['data']['phones'])) {
                    return collect($response['data']['phones'])->take(5)->map(function ($phone) {
                        return [
                            'brand' => $phone['brand'],
                            // Убираем бренд из названия модели, если он там дублируется
                            'model' => trim(str_replace($phone['brand'], '', $phone['phone_name'])),
                            'type' => 'Смартфон', // В этом API в основном смартфоны
                            'source' => 'API'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                // Если API недоступен (упал интернет), просто возвращаем пустой массив.
                // Система продолжит работать на локальной БД.
                return [];
            }

            return [];
        });
    }

    /**
     * Заглушка для получения спецификаций (которую мы использовали в TicketShow)
     */
    public function getSpecs($brand, $model): array
    {
        return [
            'Display' => '6.1 inches, OLED',
            'Chipset' => 'Octa-core Processor',
            'Battery' => 'Fast charging support',
            'OS' => 'Latest Version'
        ];
    }
}
