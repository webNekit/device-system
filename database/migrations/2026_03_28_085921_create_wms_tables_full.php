<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Склады
        Schema::create('warehouses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Места хранения (Ячейки)
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('rack')->nullable(); // Стеллаж
            $table->string('shelf')->nullable(); // Полка
            $table->string('bin')->nullable();   // Ячейка
            $table->string('label')->nullable(); // Например: A-1-12
            $table->timestamps();
        });

        // 3. Категории товаров
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('product_categories');
            $table->timestamps();
        });

        // 4. Номенклатура
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('product_categories');
            $table->integer('min_stock')->default(0); // Порог для автозакупки
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Единицы товара (Inventory Items)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products');
            $table->foreignId('storage_location_id')->constrained('storage_locations');
            $table->string('serial_number')->nullable()->index();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->string('status')->default('available'); // available, reserved, broken, in_transit
            $table->timestamps();
        });

        // 6. Транзакции (История движений)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_item_id')->constrained('inventory_items');
            $table->string('type'); // in, out, transfer, adjustment
            $table->foreignId('from_location_id')->nullable()->constrained('storage_locations');
            $table->foreignId('to_location_id')->nullable()->constrained('storage_locations');
            $table->foreignId('user_id')->constrained('users');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('warehouses');
    }
};
