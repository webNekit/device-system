<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('commission_percent')->default(0)->after('password'); // Процент мастера (например, 30)
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('labor_cost', 10, 2)->default(0)->after('priority'); // Стоимость работы мастера
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('commission_percent');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('labor_cost');
        });
    }
};
