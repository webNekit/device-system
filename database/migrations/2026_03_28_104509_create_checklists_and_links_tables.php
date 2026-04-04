<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Шаблоны чек-листов
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // intake, qc, output
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Пункты чек-листов
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->string('question');
            $table->string('field_type')->default('checkbox'); // checkbox, text
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Результаты проверок
        Schema::create('checklist_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Кто проверял
            $table->text('answers_json'); // Ответы в формате JSON
            $table->timestamps();
        });

        // 4. Магические ссылки для клиентов (Module 5)
        Schema::create('magic_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_links');
        Schema::dropIfExists('checklist_results');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
    }
};
