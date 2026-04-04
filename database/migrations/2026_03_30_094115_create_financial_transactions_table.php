<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Кто провел операцию
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete(); // Привязка к заявке (если есть)

            $table->string('type'); // income (доход), expense (расход)
            $table->string('category'); // service_payment, parts_purchase, rent, salary, other
            $table->string('payment_method'); // cash (наличные), card (карта), transfer (перевод)

            $table->decimal('amount', 10, 2); // Сумма
            $table->string('description')->nullable(); // Комментарий ("Оплата за ремонт iPhone", "Покупка кофе")

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
