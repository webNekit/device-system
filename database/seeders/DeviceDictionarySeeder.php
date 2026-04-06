<?php

namespace Database\Seeders;

use App\Domain\Ticketing\Models\DeviceBrand;
use App\Domain\Ticketing\Models\DeviceModel;
use App\Domain\Ticketing\Models\DeviceType;
use Illuminate\Database\Seeder;

class DeviceDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            'Смартфон' => [
                'Apple' => ['iPhone 11', 'iPhone 12', 'iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 14 Pro', 'iPhone 15', 'iPhone 15 Pro', 'iPhone 15 Pro Max', 'iPhone 16', 'iPhone 16 Pro'],
                'Samsung' => ['Galaxy S21', 'Galaxy S22', 'Galaxy S23', 'Galaxy S24', 'Galaxy A54', 'Galaxy A34', 'Galaxy Z Flip 5', 'Galaxy Z Fold 5'],
                'Xiaomi' => ['Redmi Note 12', 'Redmi Note 13', 'Xiaomi 13', 'Xiaomi 14', 'POCO X6'],
                'Huawei' => ['P40', 'P50', 'P60', 'Mate 50', 'Nova 11'],
            ],
            'Планшет' => [
                'Apple' => ['iPad 10', 'iPad Air', 'iPad Pro 11', 'iPad Pro 12.9', 'iPad mini 6'],
                'Samsung' => ['Galaxy Tab S9', 'Galaxy Tab S9 FE', 'Galaxy Tab A9'],
                'Xiaomi' => ['Redmi Pad', 'Xiaomi Pad 6'],
            ],
            'Ноутбук' => [
                'Apple' => ['MacBook Air 13 M1', 'MacBook Air 13 M2', 'MacBook Air 15 M2', 'MacBook Pro 14 M3', 'MacBook Pro 16 M3'],
                'ASUS' => ['VivoBook 15', 'ROG Strix G16', 'ZenBook 14', 'TUF Gaming A15'],
                'Lenovo' => ['IdeaPad 3', 'ThinkPad E14', 'Legion 5', 'Yoga 7i'],
                'HP' => ['Pavilion 15', 'ENVY x360', 'OMEN 16', 'ProBook 450'],
                'Acer' => ['Aspire 5', 'Nitro 5', 'Swift 3', 'Predator Helios'],
            ],
            'Моноблок' => [
                'Apple' => ['iMac 24 M1', 'iMac 24 M3'],
                'HP' => ['All-in-One 27', 'ProOne 440'],
            ],
            'Видеокарта' => [
                'NVIDIA' => ['GeForce RTX 4060', 'GeForce RTX 4070', 'GeForce RTX 4080', 'GeForce RTX 4090'],
                'AMD' => ['Radeon RX 7600', 'Radeon RX 7700 XT', 'Radeon RX 7800 XT', 'Radeon RX 7900 XTX'],
            ],
        ];

        foreach ($devices as $typeName => $brands) {
            $deviceType = DeviceType::firstOrCreate(['name' => $typeName]);

            foreach ($brands as $brandName => $modelNames) {
                $brand = DeviceBrand::firstOrCreate([
                    'type_id' => $deviceType->id,
                    'name' => $brandName,
                ]);

                foreach ($modelNames as $modelName) {
                    DeviceModel::firstOrCreate([
                        'brand_id' => $brand->id,
                        'name' => $modelName,
                    ]);
                }
            }
        }

        $this->command->info('✅ Справочник устройств заполнен.');
    }
}
