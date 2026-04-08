<?php

namespace Database\Seeders;

use App\Domain\Branch\Models\Branch;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\StorageLocation;
use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        $productsData = [
            [
                'sku' => 'IPH13-DISP-ORIG',
                'name' => 'Дисплей iPhone 13 (Оригинал)',
                'min_stock' => 5,
                'description' => 'Оригинальный дисплейный модуль для Apple iPhone 13.',
                'purchase_price' => 8500.00,
                'count' => 2,
            ],
            [
                'sku' => 'IPH13-BATT-OEM',
                'name' => 'Аккумулятор iPhone 13 (OEM)',
                'min_stock' => 10,
                'description' => 'OEM Аккумулятор для Apple iPhone 13.',
                'purchase_price' => 1200.00,
                'count' => 5,
            ],
            [
                'sku' => 'SAM-S23-DISP',
                'name' => 'Дисплей Samsung Galaxy S23 (Service Pack)',
                'min_stock' => 3,
                'description' => 'Оригинальный дисплейный модуль Samsung Galaxy S23.',
                'purchase_price' => 7000.00,
                'count' => 2,
            ],
            [
                'sku' => 'TYPE-C-CONN',
                'name' => 'Разъём USB-C (универсальный)',
                'min_stock' => 15,
                'description' => 'Разъём зарядки USB-C для смартфонов.',
                'purchase_price' => 350.00,
                'count' => 10,
            ],
            [
                'sku' => 'THERMAL-PASTE',
                'name' => 'Термопаста Arctic MX-4 (4г)',
                'min_stock' => 5,
                'description' => 'Термопаста для процессоров и чипов.',
                'purchase_price' => 450.00,
                'count' => 5,
            ],
        ];

        foreach ($branches as $branch) {
            $warehouse = Warehouse::firstOrCreate([
                'branch_id' => $branch->id,
                'name' => 'Основной склад ('.$branch->name.')',
            ]);

            $locations = [];
            $racks = ['A', 'B', 'C'];
            foreach ($racks as $rackIndex => $rack) {
                $locations[] = StorageLocation::firstOrCreate([
                    'warehouse_id' => $warehouse->id,
                    'rack' => $rack,
                    'shelf' => '1',
                    'bin' => '1',
                    'label' => 'Стеллаж '.$rack.' - Полка 1',
                ]);
            }

            foreach ($productsData as $productIndex => $productData) {
                $product = Product::firstOrCreate(
                    ['sku' => $productData['sku']],
                    [
                        'name' => $productData['name'],
                        'min_stock' => $productData['min_stock'],
                        'description' => $productData['description'],
                    ]
                );

                $location = $locations[$productIndex % count($locations)];

                for ($i = 0; $i < $productData['count']; $i++) {
                    InventoryItem::firstOrCreate([
                        'serial_number' => $productData['sku'].'-'.Str::upper(Str::random(6)),
                    ], [
                        'product_id' => $product->id,
                        'storage_location_id' => $location->id,
                        'purchase_price' => $productData['purchase_price'],
                        'status' => 'available',
                    ]);
                }
            }
        }

        $this->command->info('✅ Склады и номенклатура заполнены для всех филиалов.');
    }
}
