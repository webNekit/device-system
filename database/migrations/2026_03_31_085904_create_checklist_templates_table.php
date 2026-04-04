<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'diagnostics', 'qc', 'intake', 'output'
            $table->foreignId('stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('device_type_id')->nullable()->constrained('device_types')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
