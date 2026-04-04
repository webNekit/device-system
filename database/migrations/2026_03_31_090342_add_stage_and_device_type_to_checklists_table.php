<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('type')->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('device_type_id')->nullable()->after('stage_id')->constrained('device_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropForeign(['device_type_id']);
            $table->dropColumn(['stage_id', 'device_type_id']);
        });
    }
};
