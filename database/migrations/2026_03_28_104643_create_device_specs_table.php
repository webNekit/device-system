<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('device_specs'); // Принудительно удаляем, если она кривая

        Schema::create('device_specs', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->index();
            $table->string('model_name')->index();
            $table->text('specs_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_specs');
    }
};
