<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('name');
            $table->integer('order_column');
            $table->integer('sla_max_minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ulid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            // User id is currently configured as ULID by HasUlids, but standard Laravel uses integer mostly unless overridden. Wait, let's look at users migration or User model.
            // Wait, in app/Domain/Branch/Models/User.php: use HasUlids. So user id is ULID?
            // Actually, HasUlids generates a `ulid` column, it doesn't necessarily change the primary `id`. User has `ulid` method returning `['ulid']`. Usually user `id` is still bigInt. I will use foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete().
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_type');
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('serial_number')->nullable();
            $table->text('defect_description');
            $table->string('priority'); // low, normal, urgent
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->timestamp('sla_deadline_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ticket_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->integer('duration_minutes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_stage_histories');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
    }
};
