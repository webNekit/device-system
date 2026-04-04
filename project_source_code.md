# Исходный код проекта device-system

---

## Е.1 – Листинг миграции (0001_01_01_000000_create_users_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

---

## Е.2 – Листинг миграции (0001_01_01_000001_create_cache_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
```

---

## Е.3 – Листинг миграции (0001_01_01_000002_create_jobs_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
```

---

## Е.4 – Листинг миграции (2026_03_27_102233_create_permission_tables.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->id();
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        throw_if(empty($tableNames), 'Error: config/permission.php not loaded and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
```

---

## Е.5 – Листинг миграции (2026_03_27_102300_create_branches_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('timezone')->default('Europe/Moscow');
            $table->text('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
```

---

## Е.6 – Листинг миграции (2026_03_28_085006_create_customers_table_full.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('discount_percent')->default(0);
            $table->integer('min_spend')->default(0);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type')->default('individual');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('inn', 12)->nullable();
            $table->string('kpp', 9)->nullable();
            $table->text('legal_address')->nullable();
            $table->foreignId('loyalty_level_id')->nullable()->constrained('loyalty_levels');
            $table->integer('bonus_balance')->default(0);
            $table->text('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

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
```

---

## Е.7 – Листинг миграции (2026_03_28_085921_create_wms_tables_full.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('product_categories');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('product_categories');
            $table->integer('min_stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products');
            $table->foreignId('storage_location_id')->constrained('storage_locations');
            $table->string('serial_number')->nullable()->index();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_item_id')->constrained('inventory_items');
            $table->string('type');
            $table->foreignId('from_location_id')->nullable()->constrained('storage_locations');
            $table->foreignId('to_location_id')->nullable()->constrained('storage_locations');
            $table->foreignId('user_id')->constrained('users');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('warehouses');
    }
};
```

---

## Е.8 – Листинг миграции (2026_03_28_092639_create_pulse_tables.php)

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Pulse\Support\PulseMigration;

return new class extends PulseMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        Schema::create('pulse_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->mediumText('value');
            $table->index('timestamp');
            $table->index('type');
            $table->unique(['type', 'key_hash']);
        });

        Schema::create('pulse_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->bigInteger('value')->nullable();
            $table->index('timestamp');
            $table->index('type');
            $table->index('key_hash');
            $table->index(['timestamp', 'type', 'key_hash', 'value']);
        });

        Schema::create('pulse_aggregates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bucket');
            $table->unsignedMediumInteger('period');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->string('aggregate');
            $table->decimal('value', 20, 2);
            $table->unsignedInteger('count')->nullable();
            $table->unique(['bucket', 'period', 'type', 'aggregate', 'key_hash']);
            $table->index(['period', 'bucket']);
            $table->index('type');
            $table->index(['period', 'type', 'aggregate', 'bucket']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pulse_values');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_aggregates');
    }
};
```

---

## Е.9 – Листинг миграции (2026_03_28_094752_create_ticketing_tables.php)

```php
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
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_type');
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('serial_number')->nullable();
            $table->text('defect_description');
            $table->string('priority');
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
```

---

## Е.10 – Листинг миграции (2026_03_28_104509_create_checklists_and_links_tables.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->string('question');
            $table->string('field_type')->default('checkbox');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('checklist_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->text('answers_json');
            $table->timestamps();
        });

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
```

---

## Е.11 – Листинг миграции (2026_03_28_104643_create_device_specs_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('device_specs');

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
```

---

## Е.12 – Листинг миграции (2026_03_28_203057_create_ticket_inventory_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('warranty_days')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_inventory');
    }
};
```

---

## Е.13 – Листинг миграции (2026_03_29_075417_create_ticket_comments_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
```

---

## Е.14 – Листинг миграции (2026_03_30_094115_create_financial_transactions_table.php)

```php
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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('type');
            $table->string('category');
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
```

---

## Е.15 – Листинг миграции (2026_03_31_080658_create_device_dictionary_tables.php)

```php
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
```

---

## Е.16 – Листинг миграции (2026_03_31_085904_create_checklist_templates_table.php)

```php
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
            $table->string('type');
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
```

---

## Е.17 – Листинг миграции (2026_03_31_090250_create_checklist_template_items_table.php)

```php
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
            $table->string('field_type')->default('checkbox');
            $table->text('options_json')->nullable();
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
```

---

## Е.18 – Листинг миграции (2026_03_31_090342_add_stage_and_device_type_to_checklists_table.php)

```php
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
```

---

## Е.19 – Листинг миграции (2026_04_02_072031_add_hr_and_labor_columns.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('commission_percent')->default(0)->after('password');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('labor_cost', 10, 2)->default(0)->after('priority');
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
```

---

## Е.20 – Листинг модели (Branch.php)

```php
<?php

namespace App\Domain\Branch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'timezone',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

---

## Е.21 – Листинг модели (User.php)

```php
<?php

namespace App\Domain\Branch\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'commission_percent',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
```

---

## Е.22 – Листинг модели (Customer.php)

```php
<?php

namespace App\Domain\Customer\Models;

use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'inn',
        'kpp',
        'legal_address',
        'loyalty_level_id',
        'bonus_balance',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function loyaltyLevel()
    {
        return $this->belongsTo(LoyaltyLevel::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }
}
```

---

## Е.23 – Листинг модели (LoyaltyLevel.php)

```php
<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyLevel extends Model
{
    protected $fillable = [
        'name',
        'discount_percent',
        'min_spend',
    ];
}
```

---

## Е.24 – Листинг модели (FinancialTransaction.php)

```php
<?php

namespace App\Domain\Finance\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'user_id', 'ticket_id',
        'type', 'category', 'payment_method',
        'amount', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
```

---

## Е.25 – Листинг модели (InventoryItem.php)

```php
<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'product_id',
        'storage_location_id',
        'serial_number',
        'purchase_price',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
```

---

## Е.26 – Листинг модели (Product.php)

```php
<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = ['sku', 'name', 'category_id', 'min_stock', 'description'];

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function getStockCountAttribute()
    {
        return $this->items()->where('status', 'available')->count();
    }
}
```

---

## Е.27 – Листинг модели (StorageLocation.php)

```php
<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['warehouse_id', 'rack', 'shelf', 'bin', 'label'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getFullAddressAttribute()
    {
        return $this->label ?? "{$this->rack}-{$this->shelf}-{$this->bin}";
    }
}
```

---

## Е.28 – Листинг модели (Warehouse.php)

```php
<?php

namespace App\Domain\Inventory\Models;

use App\Application\Scopes\BranchIsolationScope;
use App\Domain\Branch\Models\Branch;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasUlids;

    protected static function booted()
    {
        static::addGlobalScope(new BranchIsolationScope);
    }

    protected $fillable = ['branch_id', 'name', 'description'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function locations()
    {
        return $this->hasMany(StorageLocation::class);
    }
}
```

---

## Е.29 – Листинг модели (Checklist.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'stage_id',
        'device_type_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ChecklistResult::class, 'checklist_id');
    }
}
```

---

## Е.30 – Листинг модели (ChecklistItem.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'question', 'field_type', 'sort_order'];
}
```

---

## Е.31 – Листинг модели (ChecklistResult.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistResult extends Model
{
    protected $fillable = ['ticket_id', 'checklist_id', 'user_id', 'answers_json'];

    protected function casts(): array
    {
        return [
            'answers_json' => 'array',
        ];
    }
}
```

---

## Е.32 – Листинг модели (ChecklistTemplate.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'stage_id',
        'device_type_id',
        'is_active',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistTemplateItem::class, 'template_id')->orderBy('sort_order');
    }
}
```

---

## Е.33 – Листинг модели (ChecklistTemplateItem.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistTemplateItem extends Model
{
    protected $fillable = [
        'template_id',
        'question',
        'field_type',
        'options_json',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options_json' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }
}
```

---

## Е.34 – Листинг модели (DeviceBrand.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceBrand extends Model
{
    protected $fillable = ['type_id', 'name'];

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'type_id');
    }

    public function models()
    {
        return $this->hasMany(DeviceModel::class, 'brand_id');
    }
}
```

---

## Е.35 – Листинг модели (DeviceModel.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    protected $fillable = ['brand_id', 'name'];

    public function brand()
    {
        return $this->belongsTo(DeviceBrand::class, 'brand_id');
    }
}
```

---

## Е.36 – Листинг модели (DeviceSpec.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSpec extends Model
{
    protected $fillable = ['model_name', 'brand', 'specs_json'];

    protected $casts = [
        'specs_json' => 'array',
    ];
}
```

---

## Е.37 – Листинг модели (DeviceType.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    protected $fillable = ['name'];

    public function brands()
    {
        return $this->hasMany(DeviceBrand::class, 'type_id');
    }
}
```

---

## Е.38 – Листинг модели (MagicLink.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasUlids;

    protected $table = 'magic_links';

    protected $fillable = [
        'ticket_id',
        'token',
        'expires_at',
        'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
```

---

## Е.39 – Листинг модели (Pipeline.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    protected $fillable = ['name'];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order_column');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
```

---

## Е.40 – Листинг модели (PipelineStage.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'pipeline_id',
        'name',
        'order_column',
        'sla_max_minutes',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'current_stage_id');
    }
}
```

---

## Е.41 – Листинг модели (Ticket.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use App\Application\Scopes\BranchIsolationScope;
use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Finance\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasUlids, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new BranchIsolationScope);
    }

    protected $fillable = [
        'branch_id',
        'customer_id',
        'pipeline_id',
        'current_stage_id',
        'assigned_technician_id',
        'device_type',
        'device_brand',
        'device_model',
        'serial_number',
        'defect_description',
        'priority',
        'estimated_cost',
        'sla_deadline_at',
        'labor_cost',
    ];

    protected $casts = [
        'sla_deadline_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'current_stage_id');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function usedParts()
    {
        return $this->hasMany(TicketInventory::class, 'ticket_id');
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketStageHistory::class);
    }

    public function technician()
    {
        return $this->belongsTo(\App\Domain\Branch\Models\User::class, 'assigned_technician_id');
    }
}
```

---

## Е.42 – Листинг модели (TicketComment.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Branch\Models\User;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Е.43 – Листинг модели (TicketInventory.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Inventory\Models\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketInventory extends Model
{
    protected $table = 'ticket_inventory';

    protected $fillable = [
        'ticket_id',
        'inventory_item_id',
        'selling_price',
        'warranty_days',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'warranty_days' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
```

---

## Е.44 – Листинг модели (TicketStageHistory.php)

```php
<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Branch\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStageHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'stage_id',
        'user_id',
        'entered_at',
        'exited_at',
        'duration_minutes',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Е.45 – Листинг модели (DaDataCache.php)

```php
<?php

namespace App\Application\Models;

use Illuminate\Database\Eloquent\Model;

class DaDataCache extends Model
{
    protected $table = 'dadata_cache';

    protected $fillable = [
        'query_hash',
        'response_json',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
```

---

## Е.46 – Листинг класса глобальной изоляции филиалов (BranchIsolationScope.php)

```php
<?php

namespace App\Application\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchIsolationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (auth()->hasUser()) {
            $user = auth()->user();

            if (! $user->hasRole('Admin')) {
                $builder->where($model->getTable().'.branch_id', $user->branch_id);
            }
        }
    }
}
```

---

## Е.47 – Листинг сервиса (DaDataService.php)

```php
<?php

namespace App\Application\Services;

use App\Application\Models\DaDataCache;
use Illuminate\Support\Facades\Http;

class DaDataService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.dadata.token', '');
    }

    /**
     * Поиск организации по ИНН
     */
    public function findByInn(string $inn): ?array
    {
        $hash = md5('party_'.$inn);
        $cached = DaDataCache::where('query_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if ($cached) {
            return json_decode($cached->response_json, true);
        }

        if (empty($this->token)) {
            return null;
        }

        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
            ->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party', [
                'query' => $inn,
            ]);

        if ($response->successful()) {
            $data = $response->json('suggestions.0');

            if ($data) {
                DaDataCache::create([
                    'query_hash' => $hash,
                    'response_json' => json_encode($data),
                    'expires_at' => now()->addDays(30),
                ]);
            }

            return $data;
        }

        return null;
    }
}
```

---

## Е.48 – Листинг сервиса (GSMArenaService.php)

```php
<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GSMArenaService
{
    /**
     * Поиск устройств через внешний API
     */
    public function searchDevice(string $query): array
    {
        return Cache::remember('api_device_search_' . Str::slug($query), 3600, function () use ($query) {
            try {
                $response = Http::timeout(3)->get('https://phone-specs-api.azharimm.dev/search', [
                    'query' => $query
                ]);

                if ($response->successful() && isset($response['data']['phones'])) {
                    return collect($response['data']['phones'])->take(5)->map(function ($phone) {
                        return [
                            'brand' => $phone['brand'],
                            'model' => trim(str_replace($phone['brand'], '', $phone['phone_name'])),
                            'type' => 'Смартфон',
                            'source' => 'API'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                return [];
            }

            return [];
        });
    }

    /**
     * Заглушка для получения спецификаций
     */
    public function getSpecs($brand, $model): array
    {
        return [
            'Display' => '6.1 inches, OLED',
            'Chipset' => 'Octa-core Processor',
            'Battery' => 'Fast charging support',
            'OS' => 'Latest Version'
        ];
    }
}
```

---

## Е.49 – Листинг Action (CreateBranchAction.php)

```php
<?php

namespace App\Domain\Branch\Actions;

use App\Domain\Branch\DTOs\BranchData;
use App\Domain\Branch\Models\Branch;
use Illuminate\Support\Facades\DB;

class CreateBranchAction
{
    public function execute(BranchData $data): Branch
    {
        return DB::transaction(function () use ($data) {
            return Branch::create($data->toArray());
        });
    }
}
```

---

## Е.50 – Листинг Action (CreateUserAction.php)

```php
<?php

namespace App\Domain\Branch\Actions;

use App\Domain\Branch\DTOs\UserData;
use App\Domain\Branch\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'branch_id' => $data->branch_id,
                'is_active' => true,
            ]);

            if ($data->role_name) {
                $user->assignRole($data->role_name);
            }

            return $user;
        });
    }
}
```

---

## Е.51 – Листинг Action (CreateCustomerAction.php)

```php
<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\DTOs\CustomerData;
use App\Domain\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomerAction
{
    public function execute(CustomerData $data): Customer
    {
        return DB::transaction(function () use ($data) {
            return Customer::create($data->toArray());
        });
    }
}
```

---

## Е.52 – Листинг Action (CreateTransactionAction.php)

```php
<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Finance\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;

class CreateTransactionAction
{
    public function execute(
        int $branchId,
        string $type,
        string $category,
        float $amount,
        string $paymentMethod,
        ?string $description = null,
        ?int $ticketId = null
    ): FinancialTransaction {
        return DB::transaction(function () use (
            $branchId, $type, $category, $amount, $paymentMethod, $description, $ticketId
        ) {
            return FinancialTransaction::create([
                'branch_id' => $branchId,
                'user_id' => auth()->id() ?? 1,
                'ticket_id' => $ticketId,
                'type' => $type,
                'category' => $category,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'description' => $description,
            ]);
        });
    }
}
```

---

## Е.53 – Листинг Action (AttachPartToTicketAction.php)

```php
<?php

namespace App\Domain\Ticketing\Actions;

use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketInventory;
use Exception;
use Illuminate\Support\Facades\DB;

class AttachPartToTicketAction
{
    public function execute(Ticket $ticket, $inventoryItemId, float $sellingPrice, int $warrantyDays): void
    {
        DB::transaction(function () use ($ticket, $inventoryItemId, $sellingPrice, $warrantyDays) {

            $item = InventoryItem::where('id', $inventoryItemId)
                ->where('status', 'available')
                ->first();

            if (! $item) {
                throw new Exception('Эта запчасть уже использована или не найдена на складе.');
            }

            TicketInventory::create([
                'ticket_id' => $ticket->id,
                'inventory_item_id' => $item->id,
                'selling_price' => $sellingPrice,
                'warranty_days' => $warrantyDays,
            ]);

            $item->update(['status' => 'reserved']);

            $partsTotal = $ticket->usedParts()->sum('selling_price');
            $newEstimatedCost = $partsTotal + ($ticket->labor_cost ?? 0);

            $ticket->update(['estimated_cost' => $newEstimatedCost]);
        });
    }
}
```

---

## Е.54 – Листинг Action (CreateTicketAction.php)

```php
<?php

namespace App\Domain\Ticketing\Actions;

use App\Domain\Ticketing\DTOs\CreateTicketData;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketAction
{
    public function execute(CreateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            $firstStage = PipelineStage::where('pipeline_id', $data->pipeline_id)
                ->orderBy('order_column', 'asc')
                ->first();

            if (! $firstStage) {
                throw new Exception('В этой воронке нет ни одного этапа! Добавьте этапы в настройках.');
            }

            $slaDeadline = null;
            if ($firstStage->sla_max_minutes) {
                $slaDeadline = now()->addMinutes($firstStage->sla_max_minutes);
            }

            $ticket = Ticket::create([
                'branch_id' => $data->branch_id,
                'customer_id' => $data->customer_id,
                'pipeline_id' => $data->pipeline_id,
                'current_stage_id' => $firstStage->id,
                'assigned_technician_id' => empty($data->assigned_technician_id) ? null : $data->assigned_technician_id,
                'device_type' => $data->device_type,
                'device_brand' => $data->device_brand,
                'device_model' => $data->device_model,
                'serial_number' => empty($data->serial_number) ? null : $data->serial_number,
                'defect_description' => $data->defect_description,
                'priority' => $data->priority,
                'estimated_cost' => $data->estimated_cost ?? 0,
                'sla_deadline_at' => $slaDeadline,
            ]);

            $ticket->histories()->create([
                'stage_id' => $firstStage->id,
                'user_id' => auth()->check() ? auth()->id() : null,
                'entered_at' => now(),
            ]);

            return $ticket;
        });
    }
}
```

---

## Е.55 – Листинг DTO (BranchData.php)

```php
<?php

namespace App\Domain\Branch\DTOs;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class BranchData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|string|max:255')]
        public string $address,

        #[Rule('required|timezone')]
        public string $timezone = 'Europe/Moscow',

        #[Rule('nullable|array')]
        public ?array $settings = null,
    ) {}
}
```

---

## Е.56 – Листинг DTO (UserData.php)

```php
<?php

namespace App\Domain\Branch\DTOs;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|email|unique:users,email')]
        public string $email,

        #[Rule('required|string|min:8')]
        public string $password,

        #[Rule('nullable|exists:branches,id')]
        public ?int $branch_id = null,

        #[Rule('nullable|string|exists:roles,name')]
        public ?string $role_name = null,
    ) {}
}
```

---

## Е.57 – Листинг DTO (CustomerData.php)

```php
<?php

namespace App\Domain\Customer\DTOs;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $type,
        public string $name,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $inn = null,
        public ?string $kpp = null,
        public ?string $legal_address = null,
        public ?int $loyalty_level_id = null,
        public int $bonus_balance = 0,
        public ?array $meta = null,
    ) {}
}
```

---

## Е.58 – Листинг DTO (CreateTicketData.php)

```php
<?php

namespace App\Domain\Ticketing\DTOs;

use Spatie\LaravelData\Data;

class CreateTicketData extends Data
{
    public function __construct(
        public int $branch_id,
        public string $customer_id,
        public int $pipeline_id,
        public string $device_type,
        public string $device_brand,
        public string $device_model,
        public string $defect_description,
        public string $priority,
        public ?string $serial_number = null,
        public ?float $estimated_cost = null,
        public ?string $assigned_technician_id = null
    ) {}
}
```

---

## Е.59 – Листинг Mailable (MagicLinkMail.php)

```php
<?php

namespace App\Domain\Ticketing\Mails;

use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public MagicLink $link
    ) {}

    public function build()
    {
        return $this->subject('Согласование ремонта: '.$this->ticket->device_brand)
            ->view('emails.magic-link');
    }
}
```

---

## Е.60 – Листинг Mailable (TicketReadyForPickupMail.php)

```php
<?php

namespace App\Domain\Ticketing\Mails;

use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReadyForPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваше устройство готово к выдаче!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.ready-for-pickup',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

---

## Е.61 – Листинг контроллера (Controller.php)

```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
```

---

## Е.62 – Листинг провайдера (AppServiceProvider.php)

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
```

---

## Е.63 – Листинг маршрутов (web.php)

```php
<?php

use App\Domain\Ticketing\Models\Ticket;
use App\Presentation\Livewire\Auth\Login;
use App\Presentation\Livewire\Branch\BranchManager;
use App\Presentation\Livewire\Branch\UserManager;
use App\Presentation\Livewire\Customer\CustomerManager;
use App\Presentation\Livewire\Customer\CustomerShow;
use App\Presentation\Livewire\Finance\FinanceDashboard;
use App\Presentation\Livewire\Inventory\ProductManager;
use App\Presentation\Livewire\Inventory\WarehouseManager;
use App\Presentation\Livewire\Settings\ChecklistTemplates;
use App\Presentation\Livewire\Settings\Dashboard;
use App\Presentation\Livewire\Settings\DeviceDictionary;
use App\Presentation\Livewire\Ticketing\ClientPortal;
use App\Presentation\Livewire\Ticketing\TicketManager;
use App\Presentation\Livewire\Ticketing\TicketShow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');
Route::get('/status/{token}', ClientPortal::class)->name('client.portal');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->hasRole('Storekeeper')) {
        return redirect()->route('inventory.products');
    }

    return redirect()->route('dashboard');
});
Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:Admin|Branch Manager'])->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/branches', BranchManager::class)->name('branches.index');
        Route::get('/users', UserManager::class)->name('users.index');
        Route::get('/users/{user}', \App\Presentation\Livewire\Branch\UserShow::class)->name('users.show');
        Route::get('/customers', CustomerManager::class)->name('customers.index');
        Route::get('/finance', FinanceDashboard::class)->name('finance.index');
        Route::get('/settings/devices', DeviceDictionary::class)->name('settings.devices');
        Route::get('/settings/checklists', ChecklistTemplates::class)->name('settings.checklists');
        Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show');
    });
    Route::middleware(['role:Admin|Storekeeper'])->group(function () {
        Route::get('/inventory', WarehouseManager::class)->name('inventory.index');
        Route::get('/inventory/products', ProductManager::class)->name('inventory.products');
    });
    Route::middleware(['role:Admin|Branch Manager|Technician'])->group(function () {
        Route::get('/tickets', TicketManager::class)->name('tickets.index');
        Route::get('/tickets/{ticket:ulid}', TicketShow::class)->name('tickets.show');

        Route::get('/tickets/{ticket:ulid}/invoice', function (Ticket $ticket) {
            $ticket->load(['customer', 'branch', 'usedParts.inventoryItem.product']);
            $pdf = Pdf::loadView('pdf.invoice', compact('ticket'));

            return $pdf->stream('Акт_выполненных_работ_'.$ticket->ulid.'.pdf');
        })->name('tickets.invoice');
    });
});
```

---

## Е.64 – Листинг маршрутов (console.php)

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
```

---


---

**Примечание:** Из-за большого объёма файла, ниже приведены ссылки на оставшиеся файлы. Полный код всех Livewire-компонентов, представлений, сидеров и тестов можно найти в соответствующих директориях проекта.

## Livewire-компоненты (Е.65 – Е.80)

| № | Файл | Путь |
|---|------|------|
| Е.65 | Login.php | app/Presentation/Livewire/Auth/Login.php |
| Е.66 | BranchManager.php | app/Presentation/Livewire/Branch/BranchManager.php |
| Е.67 | UserManager.php | app/Presentation/Livewire/Branch/UserManager.php |
| Е.68 | UserShow.php | app/Presentation/Livewire/Branch/UserShow.php |
| Е.69 | CustomerManager.php | app/Presentation/Livewire/Customer/CustomerManager.php |
| Е.70 | CustomerShow.php | app/Presentation/Livewire/Customer/CustomerShow.php |
| Е.71 | FinanceDashboard.php | app/Presentation/Livewire/Finance/FinanceDashboard.php |
| Е.72 | ProductManager.php | app/Presentation/Livewire/Inventory/ProductManager.php |
| Е.73 | WarehouseManager.php | app/Presentation/Livewire/Inventory/WarehouseManager.php |
| Е.74 | ChecklistTemplates.php | app/Presentation/Livewire/Settings/ChecklistTemplates.php |
| Е.75 | Dashboard.php | app/Presentation/Livewire/Settings/Dashboard.php |
| Е.76 | DeviceDictionary.php | app/Presentation/Livewire/Settings/DeviceDictionary.php |
| Е.77 | ClientPortal.php | app/Presentation/Livewire/Ticketing/ClientPortal.php |
| Е.78 | TicketKanban.php | app/Presentation/Livewire/Ticketing/TicketKanban.php |
| Е.79 | TicketManager.php | app/Presentation/Livewire/Ticketing/TicketManager.php |
| Е.80 | TicketShow.php | app/Presentation/Livewire/Ticketing/TicketShow.php |

## Представления (Е.81 – Е.105)

| № | Файл | Путь |
|---|------|------|
| Е.81 | app.blade.php (components) | resources/views/components/layouts/app.blade.php |
| Е.82 | guest.blade.php | resources/views/components/layouts/guest.blade.php |
| Е.83 | app.blade.php (layouts) | resources/views/layouts/app.blade.php |
| Е.84 | magic-link.blade.php | resources/views/emails/magic-link.blade.php |
| Е.85 | ready-for-pickup.blade.php | resources/views/emails/tickets/ready-for-pickup.blade.php |
| Е.86 | login.blade.php | resources/views/livewire/auth/login.blade.php |
| Е.87 | branch-manager.blade.php | resources/views/livewire/branch/branch-manager.blade.php |
| Е.88 | user-manager.blade.php | resources/views/livewire/branch/user-manager.blade.php |
| Е.89 | user-show.blade.php | resources/views/livewire/branch/user-show.blade.php |
| Е.90 | customer-manager.blade.php | resources/views/livewire/customer/customer-manager.blade.php |
| Е.91 | customer-show.blade.php | resources/views/livewire/customer/customer-show.blade.php |
| Е.92 | dashboard.blade.php | resources/views/livewire/dashboard.blade.php |
| Е.93 | finance-dashboard.blade.php | resources/views/livewire/finance/finance-dashboard.blade.php |
| Е.94 | product-manager.blade.php | resources/views/livewire/inventory/product-manager.blade.php |
| Е.95 | warehouse-manager.blade.php | resources/views/livewire/inventory/warehouse-manager.blade.php |
| Е.96 | checklist-templates.blade.php | resources/views/livewire/settings/checklist-templates.blade.php |
| Е.97 | settings-dashboard.blade.php | resources/views/livewire/settings/dashboard.blade.php |
| Е.98 | device-dictionary.blade.php | resources/views/livewire/settings/device-dictionary.blade.php |
| Е.99 | client-portal.blade.php | resources/views/livewire/ticketing/client-portal.blade.php |
| Е.100 | ticket-kanban.blade.php | resources/views/livewire/ticketing/ticket-kanban.blade.php |
| Е.101 | ticket-manager.blade.php | resources/views/livewire/ticketing/ticket-manager.blade.php |
| Е.102 | ticket-show.blade.php | resources/views/livewire/ticketing/ticket-show.blade.php |
| Е.103 | invoice.blade.php | resources/views/pdf/invoice.blade.php |
| Е.104 | pulse-dashboard.blade.php | resources/views/vendor/pulse/dashboard.blade.php |
| Е.105 | welcome.blade.php | resources/views/welcome.blade.php |

## Сидеры и фабрики (Е.106 – Е.115)

| № | Файл | Путь |
|---|------|------|
| Е.106 | ChecklistSeeder.php | database/seeders/ChecklistSeeder.php |
| Е.107 | ChecklistTemplatesSeeder.php | database/seeders/ChecklistTemplatesSeeder.php |
| Е.108 | CustomerSeeder.php | database/seeders/CustomerSeeder.php |
| Е.109 | DatabaseSeeder.php | database/seeders/DatabaseSeeder.php |
| Е.110 | InventorySeeder.php | database/seeders/InventorySeeder.php |
| Е.111 | RoleSeeder.php | database/seeders/RoleSeeder.php |
| Е.112 | TicketingSeeder.php | database/seeders/TicketingSeeder.php |
| Е.113 | UserRoleSeeder.php | database/seeders/UserRoleSeeder.php |
| Е.114 | UserFactory.php | database/factories/UserFactory.php |

## Тесты (Е.115 – Е.117)

| № | Файл | Путь |
|---|------|------|
| Е.115 | TestCase.php | tests/TestCase.php |
| Е.116 | ExampleTest.php (Feature) | tests/Feature/ExampleTest.php |
| Е.117 | ExampleTest.php (Unit) | tests/Unit/ExampleTest.php |

---

**Итого:** 117 файлов исходного кода проекта device-system.

