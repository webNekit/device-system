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
        $branch = Branch::first();
        if (! $branch) {
            return;
        }

        // Склад
        $warehouse = Warehouse::firstOrCreate([
            'branch_id' => $branch->id,
            'name' => 'Основной склад',
        ]);

        // Ячейка / Полка
        $location = StorageLocation::firstOrCreate([
            'warehouse_id' => $warehouse->id,
            'rack' => 'A',
            'shelf' => '1',
            'bin' => '1',
            'label' => 'Стеллаж A - Полка 1',
        ]);

        // Товар 1
        $display = Product::firstOrCreate(
            ['sku' => 'IPH13-DISP-ORIG'],
            [
                'name' => 'Дисплей iPhone 13 (Оригинал)',
                'min_stock' => 5,
                'description' => 'Оригинальный дисплейный модуль для Apple iPhone 13.',
                // category_id можно добавить, если есть справочник категорий
            ]
        );

        // Товар 2
        $battery = Product::firstOrCreate(
            ['sku' => 'IPH13-BATT-OEM'],
            [
                'name' => 'Аккумулятор iPhone 13 (OEM)',
                'min_stock' => 10,
                'description' => 'OEM Аккумулятор для Apple iPhone 13.',
            ]
        );

        // Оприходование (добавление единиц товара)
        // Добавим 2 дисплея
        for ($i = 0; $i < 2; $i++) {
            InventoryItem::firstOrCreate([
                'serial_number' => 'SN-DISP-'.Str::random(6),
            ], [
                'product_id' => $display->id,
                'storage_location_id' => $location->id,
                'purchase_price' => 8500.00,
                'status' => 'available',
            ]);
        }

        // Добавим 5 аккумуляторов
        for ($i = 0; $i < 5; $i++) {
            InventoryItem::firstOrCreate([
                'serial_number' => 'SN-BATT-'.Str::random(6),
            ], [
                'product_id' => $battery->id,
                'storage_location_id' => $location->id,
                'purchase_price' => 1200.00,
                'status' => 'available',
            ]);
        }
    }
}
