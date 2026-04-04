<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('checklist_templates')->onDelete('cascade');
            $table->string('question');
            $table->string('field_type')->default('checkbox'); // checkbox, text, select
            $table->text('options_json')->nullable(); // Для select: варианты ответов
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_template_items');
    }
};
