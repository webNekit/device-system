<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('device_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('device_types')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('device_brands')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_models');
        Schema::dropIfExists('device_brands');
        Schema::dropIfExists('device_types');
    }
};
