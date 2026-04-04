<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->decimal('selling_price', 10, 2)->default(0); // Цена продажи клиенту
            $table->integer('warranty_days')->default(30); // Гарантия в днях
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_inventory');
    }
};
