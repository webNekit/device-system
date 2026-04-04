<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Уровни лояльности
        Schema::create('loyalty_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('discount_percent')->default(0);
            $table->integer('min_spend')->default(0); // Минимальная сумма трат для перехода
            $table->timestamps();
        });

        // 2. Клиенты
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary(); // ULID как первичный ключ
            $table->string('type')->default('individual'); // individual, legal
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Поля для Юр. лиц (DaData)
            $table->string('inn', 12)->nullable();
            $table->string('kpp', 9)->nullable();
            $table->text('legal_address')->nullable();

            $table->foreignId('loyalty_level_id')->nullable()->constrained('loyalty_levels');
            $table->integer('bonus_balance')->default(0);
            $table->text('meta')->nullable(); // JSON в SQLite

            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Кеш DaData
        Schema::create('dadata_cache', function (Blueprint $table) {
            $table->id();
            $table->string('query_hash')->unique();
            $table->text('response_json');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dadata_cache');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('loyalty_levels');
    }
};
