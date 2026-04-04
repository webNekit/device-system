# Device System — Полный Код Проекта Laravel

**Дата генерации:** 2026-04-01  
**Фреймворк:** Laravel 13  
**PHP:** 8.4  
**Livewire:** 4  
**TailwindCSS:** 4  
**База Данных:** SQLite (настраиваемая)

---

## Содержание

1. [Конфигурационные файлы](#конфигурационные-файлы)
2. [app/Domain/](#appdomain)
3. [app/Application/](#appapplication)
4. [app/Presentation/](#apppresentation)
5. [app/Http/](#apphttp)
6. [app/Providers/](#appproviders)
7. [routes/](#routes)
8. [bootstrap/](#bootstrap)
9. [database/migrations/](#databasemigrations)
10. [database/seeders/](#databaseseeders)
11. [resources/views/](#resourcesviews)
12. [resources/js/](#resourcesjs)
13. [resources/css/](#resourcescss)

---

## Конфигурационные файлы

### composer.json
```json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "barryvdh/laravel-dompdf": "^3.1",
        "laravel/framework": "^13.0",
        "laravel/pulse": "^1.7",
        "laravel/tinker": "^3.0",
        "livewire/livewire": "^4.2",
        "spatie/laravel-data": "^4.20",
        "spatie/laravel-permission": "^7.2"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/boost": "^2.0",
        "laravel/pail": "^1.2.5",
        "laravel/pint": "^1.27",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^12.5.12"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force",
            "@php artisan boost:update --ansi"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### package.json
```json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "axios": "^1.11.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^3.0.0",
        "tailwindcss": "^4.0.0",
        "vite": "^8.0.0"
    }
}
```

### vite.config.js
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

### config/services.php (фрагмент DaData)
```php
<?php

return [
    // ... другие сервисы

    'dadata' => [
        'token' => env('DADATA_TOKEN'),
    ],
];
```

---

## app/Domain/

### Branch Domain

#### app/Domain/Branch/Models/Branch.php
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

#### app/Domain/Branch/Models/User.php
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

#### app/Domain/Branch/Actions/CreateBranchAction.php
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

#### app/Domain/Branch/Actions/CreateUserAction.php
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

#### app/Domain/Branch/DTOs/BranchData.php
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

#### app/Domain/Branch/DTOs/UserData.php
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

### Customer Domain

#### app/Domain/Customer/Models/Customer.php
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

#### app/Domain/Customer/Models/LoyaltyLevel.php
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

#### app/Domain/Customer/Actions/CreateCustomerAction.php
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

#### app/Domain/Customer/DTOs/CustomerData.php
```php
<?php

namespace App\Domain\Customer\DTOs;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $type, // individual, legal
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

### Finance Domain

#### app/Domain/Finance/Models/FinancialTransaction.php
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

#### app/Domain/Finance/Actions/CreateTransactionAction.php
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

### Inventory Domain

#### app/Domain/Inventory/Models/Warehouse.php
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

#### app/Domain/Inventory/Models/Product.php
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

#### app/Domain/Inventory/Models/InventoryItem.php
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

#### app/Domain/Inventory/Models/StorageLocation.php
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

### Ticketing Domain

#### app/Domain/Ticketing/Models/Ticket.php
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
}
```

#### app/Domain/Ticketing/Models/Pipeline.php
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

#### app/Domain/Ticketing/Models/PipelineStage.php
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

#### app/Domain/Ticketing/Models/TicketComment.php
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

#### app/Domain/Ticketing/Models/TicketInventory.php
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

#### app/Domain/Ticketing/Models/TicketStageHistory.php
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

#### app/Domain/Ticketing/Models/Checklist.php
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

#### app/Domain/Ticketing/Models/ChecklistItem.php
```php
<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'question', 'field_type', 'sort_order'];
}
```

#### app/Domain/Ticketing/Models/ChecklistResult.php
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

#### app/Domain/Ticketing/Models/ChecklistTemplate.php
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

#### app/Domain/Ticketing/Models/ChecklistTemplateItem.php
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

#### app/Domain/Ticketing/Models/MagicLink.php
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

#### app/Domain/Ticketing/Models/DeviceType.php
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

#### app/Domain/Ticketing/Models/DeviceBrand.php
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

#### app/Domain/Ticketing/Models/DeviceModel.php
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

#### app/Domain/Ticketing/Models/DeviceSpec.php
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

#### app/Domain/Ticketing/Actions/CreateTicketAction.php
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
            // Мягко ищем первую стадию
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

#### app/Domain/Ticketing/Actions/AttachPartToTicketAction.php
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

            // 1. Ищем запчасть и строго проверяем, что она свободна
            $item = InventoryItem::where('id', $inventoryItemId)
                ->where('status', 'available')
                ->first();

            if (! $item) {
                throw new Exception('Эта запчасть уже использована или не найдена на складе.');
            }

            // 2. Привязываем к заявке
            TicketInventory::create([
                'ticket_id' => $ticket->id,
                'inventory_item_id' => $item->id,
                'selling_price' => $sellingPrice,
                'warranty_days' => $warrantyDays,
            ]);

            // 3. Меняем статус запчасти на складе (резервируем под этот ремонт)
            $item->update(['status' => 'reserved']);

            // 4. Увеличиваем предварительную стоимость ремонта для клиента
            $ticket->increment('estimated_cost', $sellingPrice);
        });
    }
}
```

#### app/Domain/Ticketing/DTOs/CreateTicketData.php
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

#### app/Domain/Ticketing/Mails/MagicLinkMail.php
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

#### app/Domain/Ticketing/Mails/TicketReadyForPickupMail.php
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

## app/Application/

### app/Application/Scopes/BranchIsolationScope.php
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
        // 1. Игнорируем скоуп, если мы запускаем команды в консоли (php artisan)
        if (app()->runningInConsole()) {
            return;
        }

        // 2. Если есть авторизованный пользователь
        if (auth()->hasUser()) {
            $user = auth()->user();

            // 3. И если он НЕ Админ -> фильтруем по его филиалу
            if (! $user->hasRole('Admin')) {
                $builder->where($model->getTable().'.branch_id', $user->branch_id);
            }
        }
    }
}
```

### app/Application/Services/DaDataService.php
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

### app/Application/Services/GSMArenaService.php
```php
<?php

namespace App\Application\Services;

use App\Domain\Ticketing\Models\DeviceSpec;
use Illuminate\Support\Str;

class GSMArenaService
{
    /**
     * Поиск характеристик устройства
     */
    public function getSpecs(string $brand, string $model): ?array
    {
        $cached = DeviceSpec::where('brand', 'like', $brand)
            ->where('model_name', 'like', $model)
            ->first();

        if ($cached) {
            return $cached->specs_json;
        }

        // Эмуляция ответа GSMArena
        $specs = $this->mockApiResponse($brand, $model);

        if ($specs) {
            DeviceSpec::create([
                'brand' => $brand,
                'model_name' => $model,
                'specs_json' => $specs,
            ]);
        }

        return $specs;
    }

    private function mockApiResponse(string $brand, string $model): ?array
    {
        if (Str::contains(strtolower($brand), 'apple')) {
            return [
                'display' => '6.1-inch OLED',
                'chipset' => 'A17 Pro',
                'battery' => '3274 mAh',
                'camera' => '48MP Main',
            ];
        }

        if (Str::contains(strtolower($brand), 'samsung')) {
            return [
                'display' => '6.8-inch Dynamic AMOLED 2X',
                'chipset' => 'Snapdragon 8 Gen 3',
                'battery' => '5000 mAh',
                'camera' => '200MP Main',
            ];
        }

        return [
            'display' => 'N/A',
            'chipset' => 'N/A',
            'battery' => 'N/A',
            'camera' => 'N/A',
        ];
    }
}
```

### app/Application/Models/DaDataCache.php
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

## app/Presentation/

### app/Presentation/Livewire/Auth/Login.php
```php
<?php

namespace App\Presentation\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Вход в систему')]
#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            if ($user->hasRole('Storekeeper')) {
                return $this->redirectRoute('inventory.products', navigate: true);
            }

            return $this->redirectRoute('tickets.index', navigate: true);
        }

        $this->addError('email', 'Неверный логин или пароль.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
```

### app/Presentation/Livewire/Branch/BranchManager.php
```php
<?php

namespace App\Presentation\Livewire\Branch;

use App\Domain\Branch\Actions\CreateBranchAction;
use App\Domain\Branch\DTOs\BranchData;
use App\Domain\Branch\Models\Branch;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Управление филиалами')]
class BranchManager extends Component
{
    public bool $showCreateForm = false;
    public bool $showEditForm = false;

    public string $name = '';
    public string $address = '';
    public string $timezone = 'Europe/Moscow';
    public ?int $editingBranchId = null;
    public string $successMessage = '';

    #[Computed]
    public function branches()
    {
        return Branch::latest()->get();
    }

    public function save(CreateBranchAction $action)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'timezone' => 'required|string',
        ]);

        if ($this->editingBranchId) {
            $branch = Branch::findOrFail($this->editingBranchId);
            $branch->update([
                'name' => $this->name,
                'address' => $this->address,
                'timezone' => $this->timezone,
            ]);
            $this->successMessage = 'Филиал обновлен!';
            $this->showEditForm = false;
        } else {
            $dto = new BranchData(
                name: $this->name,
                address: $this->address,
                timezone: $this->timezone,
                settings: null
            );
            $action->execute($dto);
            $this->successMessage = 'Филиал успешно добавлен!';
            $this->showCreateForm = false;
        }

        $this->cancelEdit();
        unset($this->branches);
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function edit(int $id)
    {
        $branch = Branch::findOrFail($id);
        $this->editingBranchId = $id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->timezone = $branch->timezone;
        $this->showEditForm = true;
        $this->showCreateForm = false;
        $this->successMessage = '';
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'address', 'timezone', 'editingBranchId']);
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function resetForm()
    {
        $this->reset(['name', 'address', 'timezone', 'editingBranchId']);
    }

    public function delete(int $id)
    {
        $branch = Branch::findOrFail($id);
        if ($this->editingBranchId === $id) {
            $this->cancelEdit();
        }
        $branch->delete();
        $this->successMessage = 'Филиал удален!';
        unset($this->branches);
    }

    public function render()
    {
        return view('livewire.branch.branch-manager');
    }
}
```

### app/Presentation/Livewire/Branch/UserManager.php
```php
<?php

namespace App\Presentation\Livewire\Branch;

use App\Domain\Branch\Actions\CreateUserAction;
use App\Domain\Branch\DTOs\UserData;
use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Управление сотрудниками')]
class UserManager extends Component
{
    public bool $showCreateForm = false;
    public bool $showEditForm = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?int $branch_id = null;
    public string $role_name = '';
    public string $successMessage = '';

    #[Computed]
    public function users()
    {
        return User::with(['branch', 'roles'])->latest()->get();
    }

    #[Computed]
    public function branches()
    {
        return Branch::orderBy('name')->get();
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->get();
    }

    public function getRoleNamesMap()
    {
        return [
            'Admin' => 'Администратор',
            'Branch Manager' => 'Управляющий',
            'Technician' => 'Инженер',
            'Storekeeper' => 'Кладовщик',
            'Accountant' => 'Бухгалтер',
        ];
    }

    public function save(CreateUserAction $action)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'required|exists:branches,id',
            'role_name' => 'required|exists:roles,name',
        ]);

        $dto = new UserData(
            name: $this->name,
            email: $this->email,
            password: $this->password,
            branch_id: $this->branch_id,
            role_name: $this->role_name
        );

        $action->execute($dto);

        $this->reset(['name', 'email', 'password', 'branch_id', 'role_name']);
        $this->successMessage = 'Сотрудник успешно добавлен!';
        $this->showCreateForm = false;
        unset($this->users);
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function edit($userId)
    {
        $this->resetForm();
        $user = User::with('roles')->findOrFail($userId);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->branch_id = $user->branch_id;
        $this->role_name = $user->roles->first()?->name ?? '';
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function delete($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        $this->successMessage = 'Сотрудник удален!';
        unset($this->users);
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'branch_id', 'role_name']);
    }

    public function render()
    {
        return view('livewire.branch.user-manager');
    }
}
```

### app/Presentation/Livewire/Customer/CustomerManager.php
```php
<?php

namespace App\Presentation\Livewire\Customer;

use App\Application\Services\DaDataService;
use App\Domain\Customer\Actions\CreateCustomerAction;
use App\Domain\Customer\DTOs\CustomerData;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\LoyaltyLevel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Умная CRM')]
class CustomerManager extends Component
{
    use WithPagination;

    public bool $showCreateForm = false;
    public string $type = 'individual';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $inn = '';
    public string $kpp = '';
    public string $legal_address = '';
    public ?int $loyalty_level_id = null;
    public ?string $editingCustomerId = null;
    public string $successMessage = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Computed]
    public function loyaltyLevels()
    {
        return LoyaltyLevel::all();
    }

    #[Computed]
    public function customers()
    {
        return Customer::with('loyaltyLevel')
            ->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('phone', 'like', '%'.$this->search.'%')
            ->orWhere('inn', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);
    }

    public function updatedInn($value, DaDataService $service)
    {
        if ($this->type === 'legal' && strlen($value) >= 10) {
            $data = $service->findByInn($value);
            if ($data) {
                $this->name = $data['value'] ?? '';
                $this->kpp = $data['data']['kpp'] ?? '';
                $this->legal_address = $data['data']['address']['value'] ?? '';
                $this->successMessage = 'Данные организации подгружены из DaData';
            }
        }
    }

    public function save(CreateCustomerAction $action)
    {
        $this->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'inn' => 'nullable|string|max:12',
            'kpp' => 'nullable|string|max:9',
        ]);

        $data = new CustomerData(
            type: $this->type,
            name: $this->name,
            phone: $this->phone,
            email: $this->email,
            inn: $this->inn,
            kpp: $this->kpp,
            legal_address: $this->legal_address,
            loyalty_level_id: $this->loyalty_level_id,
        );

        if ($this->editingCustomerId) {
            $customer = Customer::findOrFail($this->editingCustomerId);
            $customer->update($data->toArray());
            $this->successMessage = 'Данные клиента обновлены';
        } else {
            $action->execute($data);
            $this->successMessage = 'Клиент успешно добавлен';
        }

        $this->cancelEdit();
        $this->showCreateForm = false;
    }

    public function openCreate()
    {
        $this->cancelEdit();
        $this->showCreateForm = true;
    }

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        $this->editingCustomerId = $id;
        $this->type = $customer->type;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->email = $customer->email ?? '';
        $this->inn = $customer->inn ?? '';
        $this->kpp = $customer->kpp ?? '';
        $this->legal_address = $customer->legal_address ?? '';
        $this->loyalty_level_id = $customer->loyalty_level_id;
        $this->successMessage = '';
        $this->showCreateForm = true;
    }

    public function cancelEdit()
    {
        $this->reset(['type', 'name', 'phone', 'email', 'inn', 'kpp', 'legal_address', 'loyalty_level_id', 'editingCustomerId']);
        $this->showCreateForm = false;
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
    }

    public function delete(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        $this->successMessage = 'Клиент удален';
        if ($this->editingCustomerId === $id) {
            $this->cancelEdit();
        }
    }

    public function render()
    {
        return view('livewire.customer.customer-manager');
    }
}
```

### app/Presentation/Livewire/Customer/CustomerShow.php
```php
<?php

namespace App\Presentation\Livewire\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Профиль клиента')]
class CustomerShow extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load('loyaltyLevel');
    }

    #[Computed]
    public function stats()
    {
        $tickets = Ticket::where('customer_id', $this->customer->id)->get();
        $active = $tickets->where('currentStage.order_column', '<', 7)->count();
        $completed = $tickets->where('currentStage.order_column', '>=', 7)
            ->where('estimated_cost', '>', 0);
        $rejected = $tickets->where('currentStage.order_column', '>=', 7)
            ->where('estimated_cost', 0)->count();
        $ltv = $completed->sum('estimated_cost');

        return [
            'total' => $tickets->count(),
            'active' => $active,
            'completed' => $completed->count(),
            'rejected' => $rejected,
            'ltv' => $ltv,
        ];
    }

    #[Computed]
    public function tickets()
    {
        return Ticket::where('customer_id', $this->customer->id)
            ->with(['currentStage', 'usedParts.inventoryItem.product'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('device_brand', 'like', '%'.$this->search.'%')
                        ->orWhere('device_model', 'like', '%'.$this->search.'%')
                        ->orWhere('ulid', 'like', '%'.$this->search.'%')
                        ->orWhere('defect_description', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.customer.customer-show');
    }
}
```

### app/Presentation/Livewire/Finance/FinanceDashboard.php
```php
<?php

namespace App\Presentation\Livewire\Finance;

use App\Domain\Branch\Models\Branch;
use App\Domain\Finance\Actions\CreateTransactionAction;
use App\Domain\Finance\Models\FinancialTransaction;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Финансы и P&L')]
class FinanceDashboard extends Component
{
    public ?int $branch_id = null;
    public string $period = 'this_month';
    public bool $showForm = false;
    public string $type = 'expense';
    public string $category = 'other';
    public string $payment_method = 'cash';
    public float $amount = 0;
    public string $description = '';
    public ?int $form_branch_id = null;
    public string $successMessage = '';

    public function mount()
    {
        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
            $this->form_branch_id = auth()->user()->branch_id;
        }
    }

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function dateRange()
    {
        return match ($this->period) {
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::createFromTimestamp(0), Carbon::now()],
        };
    }

    #[Computed]
    public function analytics()
    {
        $query = FinancialTransaction::whereBetween('created_at', $this->dateRange);
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        $transactions = $query->get();

        $revenue = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');

        $cogsQuery = Ticket::whereBetween('updated_at', $this->dateRange)
            ->whereHas('currentStage', fn ($q) => $q->where('order_column', '>=', 7))
            ->with(['usedParts.inventoryItem']);

        if ($this->branch_id) {
            $cogsQuery->where('branch_id', $this->branch_id);
        }

        $cogs = $cogsQuery->get()->flatMap(function ($ticket) {
            return $ticket->usedParts->map(fn ($part) => $part->inventoryItem->purchase_price ?? 0);
        })->sum();

        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        return compact('revenue', 'expenses', 'cogs', 'grossProfit', 'netProfit', 'margin');
    }

    #[Computed]
    public function recentTransactions()
    {
        $query = FinancialTransaction::with(['user', 'ticket.customer'])->latest();
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        return $query->take(20)->get();
    }

    public function saveTransaction(CreateTransactionAction $action)
    {
        $this->validate([
            'form_branch_id' => 'required|exists:branches,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer',
            'description' => 'required|string|max:255',
        ]);

        $action->execute(
            (int) $this->form_branch_id,
            $this->type,
            $this->category,
            (float) $this->amount,
            $this->payment_method,
            $this->description
        );

        $this->reset(['showForm', 'type', 'category', 'amount', 'payment_method', 'description']);
        $this->type = 'expense';
        $this->category = 'other';
        $this->payment_method = 'cash';
        $this->successMessage = 'Финансовая операция проведена!';
        unset($this->analytics);
        unset($this->recentTransactions);
    }

    public function render()
    {
        return view('livewire.finance.finance-dashboard');
    }
}
```

### app/Presentation/Livewire/Inventory/WarehouseManager.php
```php
<?php

namespace App\Presentation\Livewire\Inventory;

use App\Domain\Branch\Models\Branch;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Inventory\Models\StorageLocation;
use App\Domain\Inventory\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('WMS: Склады и ячейки')]
class WarehouseManager extends Component
{
    public string $name = '';
    public ?int $branch_id = null;
    public string $description = '';
    public ?string $selectedWarehouseId = null;
    public string $rack = '';
    public string $shelf = '';
    public string $bin = '';
    public ?int $viewingLocationId = null;
    public string $successMessage = '';

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::with(['branch', 'locations'])->latest()->get();
    }

    #[Computed]
    public function locationItems()
    {
        if (! $this->viewingLocationId) {
            return collect();
        }
        return InventoryItem::with('product')
            ->where('storage_location_id', $this->viewingLocationId)
            ->where('status', 'available')
            ->latest()
            ->get();
    }

    public function createWarehouse()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        Warehouse::create([
            'name' => $this->name,
            'branch_id' => $this->branch_id,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'branch_id', 'description']);
        $this->successMessage = 'Склад успешно создан';
        unset($this->warehouses);
    }

    public function selectWarehouse(string $id)
    {
        $this->selectedWarehouseId = $id;
        $this->viewingLocationId = null;
        $this->successMessage = '';
    }

    public function addLocation()
    {
        $this->validate([
            'selectedWarehouseId' => 'required|exists:warehouses,id',
            'rack' => 'required|string',
            'shelf' => 'required|string',
            'bin' => 'required|string',
        ]);

        StorageLocation::create([
            'warehouse_id' => $this->selectedWarehouseId,
            'rack' => $this->rack,
            'shelf' => $this->shelf,
            'bin' => $this->bin,
            'label' => "{$this->rack}-{$this->shelf}-{$this->bin}",
        ]);

        $this->reset(['rack', 'shelf', 'bin']);
        $this->successMessage = 'Ячейка добавлена';
        unset($this->warehouses);
    }

    public function viewLocation(int $locationId)
    {
        $this->viewingLocationId = $locationId;
        $this->resetErrorBag('location_error');
    }

    public function deleteLocation(int $id)
    {
        $hasItems = InventoryItem::where('storage_location_id', $id)->where('status', 'available')->exists();
        if ($hasItems) {
            $this->addError('location_error', 'Нельзя удалить ячейку: в ней лежат запчасти!');
            return;
        }
        StorageLocation::findOrFail($id)->delete();
        $this->successMessage = 'Ячейка удалена';
        if ($this->viewingLocationId === $id) {
            $this->viewingLocationId = null;
        }
        unset($this->warehouses);
    }

    public function deleteWarehouse(string $id)
    {
        Warehouse::findOrFail($id)->delete();
        $this->successMessage = 'Склад удален';
        unset($this->warehouses);
    }

    public function mount()
    {
        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
        }
    }

    public function render()
    {
        return view('livewire.inventory.warehouse-manager');
    }
}
```

### app/Presentation/Livewire/Inventory/ProductManager.php
```php
<?php

namespace App\Presentation\Livewire\Inventory;

use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('WMS: Номенклатура и остатки')]
class ProductManager extends Component
{
    use WithPagination;

    public string $sku = '';
    public string $name = '';
    public int $min_stock = 0;
    public ?string $selectedProductId = null;
    public int $quantity = 1;
    public string|int|null $selectedLocationId = null;
    public string $serial_number = '';
    public float $purchase_price = 0;
    public string $successMessage = '';

    #[Computed]
    public function products()
    {
        return Product::withCount(['items as stock' => function ($query) {
            $query->where('status', 'available');
        }])->latest()->paginate(10);
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::with('locations')->get();
    }

    public function createProduct()
    {
        $this->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
        ]);

        Product::create([
            'sku' => $this->sku,
            'name' => $this->name,
            'min_stock' => $this->min_stock,
        ]);

        $this->reset(['sku', 'name', 'min_stock']);
        $this->successMessage = 'Товар добавлен в номенклатуру';
    }

    public function stockIn()
    {
        $this->validate([
            'selectedProductId' => 'required|exists:products,id',
            'selectedLocationId' => 'required|exists:storage_locations,id',
            'purchase_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:500',
        ]);

        for ($i = 0; $i < $this->quantity; $i++) {
            InventoryItem::create([
                'product_id' => $this->selectedProductId,
                'storage_location_id' => $this->selectedLocationId,
                'serial_number' => ($this->quantity === 1 && $this->serial_number) ? $this->serial_number : null,
                'purchase_price' => $this->purchase_price,
                'status' => 'available',
            ]);
        }

        $this->reset(['selectedProductId', 'selectedLocationId', 'serial_number', 'purchase_price']);
        $this->quantity = 1;
        $this->successMessage = "Успешно оприходовано: {$this->quantity} шт.";
        unset($this->products);
    }

    public function generateSku()
    {
        $this->sku = 'ITM-' . strtoupper(\Illuminate\Support\Str::random(5));
        $this->resetErrorBag('sku');
    }

    public function render()
    {
        return view('livewire.inventory.product-manager');
    }
}
```

### app/Presentation/Livewire/Ticketing/TicketManager.php
```php
<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Domain\Branch\Models\Branch;
use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Actions\CreateTicketAction;
use App\Domain\Ticketing\DTOs\CreateTicketData;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\Pipeline;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Канбан-доска заявок')]
class TicketManager extends Component
{
    public bool $showCreateForm = false;
    public string|int|null $branch_id = null;
    public string|int|null $customer_id = null;
    public string|int|null $pipeline_id = null;
    public ?string $serial_number = '';
    public ?string $defect_description = '';
    public ?string $priority = 'normal';
    public array $visibleStageIds = [];
    public ?int $selectedPipelineId = null;
    public ?string $device_type = null;
    public ?string $device_brand = null;
    public ?string $device_model = null;

    public function mount()
    {
        $this->visibleStageIds = PipelineStage::pluck('id')->map(fn ($id) => (string) $id)->toArray();
        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
        }
        $firstPipeline = Pipeline::first();
        if ($firstPipeline) {
            $this->selectedPipelineId = $firstPipeline->id;
        }
    }

    #[Computed]
    public function deviceTypes()
    {
        return DeviceType::all();
    }

    #[Computed]
    public function deviceDict()
    {
        $dict = [];
        $types = DeviceType::with(['brands.models'])->get();
        foreach ($types as $type) {
            $dict[$type->name] = [];
            foreach ($type->brands as $brand) {
                $dict[$type->name][$brand->name] = $brand->models->pluck('name')->toArray();
            }
        }
        return $dict;
    }

    #[Computed]
    public function pipelines()
    {
        return Pipeline::all();
    }

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function customers()
    {
        return Customer::all();
    }

    #[Computed]
    public function stages()
    {
        if (! $this->selectedPipelineId) {
            return collect();
        }
        return PipelineStage::where('pipeline_id', $this->selectedPipelineId)
            ->with(['tickets' => function ($query) {
                $query->with(['customer', 'histories'])->latest();
            }])
            ->orderBy('order_column')
            ->get();
    }

    public function save(CreateTicketAction $action)
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'pipeline_id' => 'required|exists:pipelines,id',
            'device_type' => 'required|string',
            'device_brand' => 'required|string',
            'device_model' => 'required|string',
            'defect_description' => 'required|string|max:1000',
            'priority' => 'required|in:low,normal,urgent',
        ]);

        $dto = new CreateTicketData(
            branch_id: $this->branch_id,
            customer_id: $this->customer_id,
            pipeline_id: $this->pipeline_id,
            assigned_technician_id: null,
            device_type: $this->device_type,
            device_brand: $this->device_brand,
            device_model: $this->device_model,
            serial_number: empty($this->serial_number) ? null : $this->serial_number,
            defect_description: $this->defect_description,
            priority: $this->priority,
            estimated_cost: 0
        );

        try {
            $action->execute($dto);
            $targetPipelineId = $this->pipeline_id;
            $this->reset(['branch_id', 'customer_id', 'pipeline_id', 'device_type', 'device_brand', 'device_model', 'serial_number', 'defect_description', 'priority']);
            if (! auth()->user()->hasRole('Admin')) {
                $this->branch_id = auth()->user()->branch_id;
            }
            $this->showCreateForm = false;
            $this->selectedPipelineId = $targetPipelineId;
            unset($this->stages);
        } catch (\Throwable $e) {
            $this->addError('pipeline_id', $e->getMessage());
        }
    }

    public function moveTicket($ticketId, $nextStageId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $currentHistory = $ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update(['exited_at' => now(), 'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at)]);
        }
        $ticket->update(['current_stage_id' => $nextStageId]);
        $ticket->histories()->create(['stage_id' => $nextStageId, 'user_id' => auth()->id() ?? 1, 'entered_at' => now()]);
        unset($this->stages);
    }

    public function render()
    {
        return view('livewire.ticketing.ticket-manager');
    }
}
```

### app/Presentation/Livewire/Ticketing/TicketShow.php
```php
<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Application\Services\GSMArenaService;
use App\Domain\Branch\Models\User;
use App\Domain\Finance\Actions\CreateTransactionAction;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Ticketing\Actions\AttachPartToTicketAction;
use App\Domain\Ticketing\Mails\MagicLinkMail;
use App\Domain\Ticketing\Mails\TicketReadyForPickupMail;
use App\Domain\Ticketing\Models\Checklist;
use App\Domain\Ticketing\Models\ChecklistResult;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketComment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Детали заявки')]
class TicketShow extends Component
{
    public Ticket $ticket;
    public array $specs = [];
    public array $checklistAnswers = [];
    public string $successMessage = '';
    public string $newComment = '';
    public bool $showPartForm = false;
    public string $searchPartSku = '';
    public int|string|null $selectedPartId = null;
    public float|string $partSellingPrice = 0;
    public int|string $partWarrantyDays = 30;

    public function mount(Ticket $ticket, GSMArenaService $specsService)
    {
        $this->ticket = $ticket->load([
            'customer',
            'currentStage',
            'histories.stage',
            'usedParts.inventoryItem.product',
            'comments.user',
        ]);
        $this->specs = $specsService->getSpecs($ticket->device_brand, $ticket->device_model) ?? [];
    }

    #[Computed]
    public function currentChecklist()
    {
        $type = match ($this->ticket->currentStage->order_column) {
            1 => 'intake',
            6 => 'qc',
            7 => 'output',
            8 => 'output',
            default => null
        };

        if (! $type) {
            return null;
        }

        $checklist = Checklist::with('items')
            ->where('type', $type)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stage_id')
                    ->orWhere('stage_id', $this->ticket->current_stage_id);
            })
            ->where(function ($q) {
                $deviceType = DeviceType::where('name', $this->ticket->device_type)->first();
                if ($deviceType) {
                    $q->whereNull('device_type_id')
                        ->orWhere('device_type_id', $deviceType->id);
                } else {
                    $q->whereNull('device_type_id');
                }
            })
            ->orderBy('sort_order')
            ->first();

        return $checklist;
    }

    #[Computed]
    public function availableParts()
    {
        if (! $this->showPartForm) {
            return collect();
        }

        $query = InventoryItem::with('product')->where('status', 'available');

        if (! empty($this->searchPartSku)) {
            $query->where(function ($q) {
                $q->whereHas('product', function ($subQ) {
                    $subQ->where('name', 'like', '%'.$this->searchPartSku.'%')
                        ->orWhere('sku', 'like', '%'.$this->searchPartSku.'%');
                })->orWhere('serial_number', 'like', '%'.$this->searchPartSku.'%');
            });
        }

        return $query->take(15)->get();
    }

    public function attachPart(AttachPartToTicketAction $action)
    {
        $this->validate([
            'selectedPartId' => [
                'required',
                Rule::exists('inventory_items', 'id')->where('status', 'available'),
            ],
            'partSellingPrice' => 'required|numeric|min:0',
            'partWarrantyDays' => 'required|integer|min:0',
        ], [
            'selectedPartId.exists' => 'Эта деталь уже зарезервирована или списана.',
        ]);

        $action->execute(
            $this->ticket,
            $this->selectedPartId,
            (float) $this->partSellingPrice,
            (int) $this->partWarrantyDays
        );

        $this->reset(['showPartForm', 'searchPartSku', 'selectedPartId', 'partSellingPrice']);
        $this->partWarrantyDays = 30;
        $this->ticket->refresh();
        $this->ticket->load(['usedParts.inventoryItem.product']);
    }

    public function saveChecklist()
    {
        $checklist = $this->currentChecklist;
        if (! $checklist) {
            return;
        }

        $userId = auth()->id() ?? User::first()->id ?? 1;

        ChecklistResult::create([
            'ticket_id' => $this->ticket->id,
            'checklist_id' => $checklist->id,
            'user_id' => $userId,
            'answers_json' => $this->checklistAnswers,
        ]);

        $this->successMessage = 'Чек-лист успешно заполнен и сохранен в базе!';
        $this->checklistAnswers = [];
    }

    public function addComment()
    {
        $this->validate(['newComment' => 'required|string|max:2000']);

        $this->ticket->comments()->create([
            'user_id' => auth()->id() ?? 1,
            'content' => $this->newComment,
        ]);

        $this->reset('newComment');
        $this->ticket->refresh();
        $this->ticket->load(['comments.user']);
    }

    public function generateMagicLink()
    {
        $link = MagicLink::create([
            'ticket_id' => $this->ticket->id,
            'token' => Str::random(32),
        ]);
        if ($this->ticket->customer->email) {
            Mail::to($this->ticket->customer->email)->queue(new MagicLinkMail($this->ticket, $link));
            $this->successMessage = 'Ссылка создана и письмо УЛЕТЕЛО клиенту на: '.$this->ticket->customer->email;
        } else {
            $this->successMessage = 'Ссылка создана (Email клиента не указан): '.route('client.portal', $link->token);
        }
    }

    #[Computed]
    public function nextStage()
    {
        return PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->where('order_column', '>', $this->ticket->currentStage->order_column)
            ->orderBy('order_column', 'asc')
            ->first();
    }

    public function moveToNextStage()
    {
        if ($this->ticket->currentStage->order_column == 3) {
            $this->addError('stage_error', 'Ожидайте ответа! Клиент должен согласовать ремонт по ссылке.');
            return;
        }

        $next = $this->nextStage;
        if (! $next) {
            return;
        }

        $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update([
                'exited_at' => now(),
                'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
            ]);
        }

        $this->ticket->update(['current_stage_id' => $next->id]);

        $this->ticket->histories()->create([
            'stage_id' => $next->id,
            'user_id' => auth()->id() ?? 1,
            'entered_at' => now(),
        ]);

        $this->successMessage = 'Заявка переведена на этап: '.$next->name;
        $this->ticket->refresh();
        $this->ticket->load(['currentStage', 'histories.stage']);
    }

    public function generateInvoiceAndClose(CreateTransactionAction $financeAction)
    {
        $closedStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->orderBy('order_column', 'desc')
            ->first();

        if ($closedStage && $this->ticket->current_stage_id !== $closedStage->id) {
            $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
            if ($currentHistory) {
                $currentHistory->update([
                    'exited_at' => now(),
                    'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
                ]);
            }

            $this->ticket->update(['current_stage_id' => $closedStage->id]);

            $this->ticket->histories()->create([
                'stage_id' => $closedStage->id,
                'user_id' => auth()->id() ?? 1,
                'entered_at' => now(),
            ]);

            TicketComment::create([
                'ticket_id' => $this->ticket->id,
                'user_id' => null,
                'content' => '📄 Сгенерирован Акт выполненных работ. Устройство выдано клиенту. Оплата получена.',
            ]);

            if ($this->ticket->customer->email) {
                Mail::to($this->ticket->customer->email)->queue(new TicketReadyForPickupMail($this->ticket));
            }

            if ($this->ticket->estimated_cost > 0) {
                $financeAction->execute(
                    branchId: $this->ticket->branch_id,
                    type: 'income',
                    category: 'service_payment',
                    amount: $this->ticket->estimated_cost,
                    paymentMethod: 'card',
                    description: 'Оплата ремонта '.$this->ticket->device_brand.' '.$this->ticket->device_model,
                    ticketId: $this->ticket->id
                );
            }

            $this->ticket->refresh();
            $this->ticket->load(['currentStage', 'histories.stage', 'comments.user']);
        }

        $this->js("window.open('/tickets/{$this->ticket->ulid}/invoice', '_blank');");
    }

    public function render()
    {
        return view('livewire.ticketing.ticket-show');
    }
}
```

### app/Presentation/Livewire/Ticketing/ClientPortal.php
```php
<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketComment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Статус вашего ремонта')]
#[Layout('components.layouts.guest')]
class ClientPortal extends Component
{
    public Ticket $ticket;
    public MagicLink $magicLink;
    public bool $isApproved = false;

    public function mount(string $token)
    {
        $this->magicLink = MagicLink::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        $this->magicLink->update(['last_visited_at' => now()]);
        $this->ticket = $this->magicLink->ticket->load(['customer', 'currentStage', 'usedParts.inventoryItem.product']);
    }

    private function performStageChange($newStageId)
    {
        $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update([
                'exited_at' => now(),
                'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
            ]);
        }

        $this->ticket->update(['current_stage_id' => $newStageId]);

        $this->ticket->histories()->create([
            'stage_id' => $newStageId,
            'user_id' => null,
            'entered_at' => now(),
        ]);
    }

    public function approveRepair()
    {
        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'content' => '✅ КЛИЕНТ СОГЛАСОВАЛ СУММУ РЕМОНТА ('.number_format($this->ticket->estimated_cost, 0, '.', ' ').' ₽)',
        ]);

        $nextStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->where('order_column', '>', $this->ticket->currentStage->order_column)
            ->orderBy('order_column', 'asc')
            ->first();

        if ($nextStage) {
            $this->performStageChange($nextStage->id);
        }

        $this->isApproved = true;
    }

    public function rejectRepair()
    {
        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'content' => '❌ КЛИЕНТ ОТКАЗАЛСЯ ОТ РЕМОНТА. Заявка автоматически закрыта.',
        ]);

        $closedStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->orderBy('order_column', 'desc')
            ->first();

        if ($closedStage) {
            $this->performStageChange($closedStage->id);
        }

        $this->isApproved = true;
    }

    public function render()
    {
        return view('livewire.ticketing.client-portal');
    }
}
```

### app/Presentation/Livewire/Settings/Dashboard.php
```php
<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Finance\Models\FinancialTransaction;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Панель управления')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'total_users' => User::count(),
            'total_branches' => Branch::count(),
            'total_tickets' => Ticket::count(),
            'tickets_in_work' => Ticket::whereHas('currentStage', function ($q) {
                $q->where('order_column', '<', 7);
            })->count(),
            'tickets_completed' => Ticket::whereHas('currentStage', function ($q) {
                $q->where('order_column', '>=', 7);
            })->count(),
        ];

        $pipelineStats = PipelineStage::withCount('tickets')
            ->orderBy('order_column')
            ->get()
            ->map(fn ($stage) => [
                'name' => $stage->name,
                'count' => $stage->tickets_count,
                'order' => $stage->order_column,
            ]);

        $financeStats = [
            'total_income' => FinancialTransaction::where('type', 'income')->sum('amount'),
            'total_expense' => FinancialTransaction::where('type', 'expense')->sum('amount'),
            'today_income' => FinancialTransaction::where('type', 'income')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'today_expense' => FinancialTransaction::where('type', 'expense')
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        $recentTickets = Ticket::with(['customer', 'currentStage', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        $topCustomers = Customer::withCount('tickets')
            ->orderBy('tickets_count', 'desc')
            ->take(5)
            ->get();

        $deviceStats = Ticket::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        $priorityStats = Ticket::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->orderByDesc('count')
            ->get();

        return view('livewire.settings.dashboard', compact(
            'stats',
            'pipelineStats',
            'financeStats',
            'recentTickets',
            'topCustomers',
            'deviceStats',
            'priorityStats'
        ));
    }
}
```

### app/Presentation/Livewire/Settings/DeviceDictionary.php
```php
<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Ticketing\Models\DeviceBrand;
use App\Domain\Ticketing\Models\DeviceModel;
use App\Domain\Ticketing\Models\DeviceType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Справочник: Устройства')]
class DeviceDictionary extends Component
{
    use WithFileUploads;

    public $file;
    public bool $clearBeforeImport = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $path = $this->file->getRealPath();
            $rawContent = file_get_contents($path);
            $firstBytes = substr($rawContent, 0, 4);
            $content = $rawContent;

            if (substr($firstBytes, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($rawContent, 3);
            } elseif (substr($firstBytes, 0, 2) === "\xFF\xFE") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
            } elseif (substr($firstBytes, 0, 2) === "\xFE\xFF") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16BE');
            } elseif (substr($firstBytes, 0, 4) === "\xFF\xFE\x00\x00") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-32');
            } else {
                $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'KOI8-R', 'ISO-8859-5', 'UTF-16LE', 'UTF-16BE'], true);
                if ($encoding && $encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                } elseif (! $encoding) {
                    $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
                }
            }

            $tempPath = storage_path('app/temp_import.csv');
            file_put_contents($tempPath, $content);
            $fileHandle = fopen($tempPath, 'r');
            $delimiter = strpos($content, ';') !== false ? ';' : ',';

            DB::beginTransaction();

            if ($this->clearBeforeImport) {
                DeviceModel::truncate();
                DeviceBrand::truncate();
                DeviceType::truncate();
            }

            $rowCounter = 0;
            $firstRow = true;
            while (($row = fgetcsv($fileHandle, 1000, $delimiter)) !== false) {
                if (count($row) < 3 || mb_strtolower(trim($row[0])) === 'тип' || empty(trim($row[0]))) {
                    continue;
                }

                $typeName = trim(str_replace(',', '', $row[0]));
                $brandName = trim(str_replace(',', '', $row[1]));
                $modelName = trim(str_replace(',', '', $row[2]));

                $typeName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $typeName);
                $brandName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $brandName);
                $modelName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $modelName);

                if (empty($typeName) || empty($brandName) || empty($modelName)) {
                    continue;
                }

                $type = DeviceType::firstOrCreate(['name' => $typeName]);
                $brand = DeviceBrand::firstOrCreate(['type_id' => $type->id, 'name' => $brandName]);
                DeviceModel::firstOrCreate(['brand_id' => $brand->id, 'name' => $modelName]);

                $rowCounter++;
            }

            fclose($fileHandle);
            @unlink($tempPath);
            DB::commit();

            $this->reset('file');
            $this->successMessage = "Успешно загружено/обновлено: {$rowCounter} записей!";
            $this->errorMessage = '';

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorMessage = 'Ошибка импорта: '.$e->getMessage();
            $this->successMessage = '';
        }
    }

    public function render()
    {
        $types = DeviceType::with(['brands.models'])->get();
        return view('livewire.settings.device-dictionary', compact('types'));
    }
}
```

### app/Presentation/Livewire/Settings/ChecklistTemplates.php
```php
<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Ticketing\Models\ChecklistTemplate;
use App\Domain\Ticketing\Models\ChecklistTemplateItem;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\PipelineStage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Справочник: Чек-листы')]
class ChecklistTemplates extends Component
{
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public bool $showItemsForm = false;
    public ?int $editingTemplateId = null;
    public string $templateName = '';
    public string $templateType = 'diagnostics';
    public ?int $templateStageId = null;
    public ?int $templateDeviceTypeId = null;
    public string $templateDescription = '';
    public bool $templateIsActive = true;
    public array $items = [];
    public string $newItemQuestion = '';
    public string $newItemType = 'checkbox';
    public string $newItemOptions = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->templateName = '';
        $this->templateType = 'diagnostics';
        $this->templateStageId = null;
        $this->templateDeviceTypeId = null;
        $this->templateDescription = '';
        $this->templateIsActive = true;
        $this->editingTemplateId = null;
        $this->items = [];
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function openEdit($id)
    {
        $template = ChecklistTemplate::findOrFail($id);
        $this->editingTemplateId = $id;
        $this->templateName = $template->name;
        $this->templateType = $template->type;
        $this->templateStageId = $template->stage_id;
        $this->templateDeviceTypeId = $template->device_type_id;
        $this->templateDescription = $template->description ?? '';
        $this->templateIsActive = $template->is_active;
        $this->showEditForm = true;
    }

    public function openItemsManager($id)
    {
        $template = ChecklistTemplate::with('items')->findOrFail($id);
        $this->editingTemplateId = $id;
        $this->items = $template->items->map(fn ($item) => [
            'id' => $item->id,
            'question' => $item->question,
            'field_type' => $item->field_type,
            'options_json' => $item->options_json,
            'sort_order' => $item->sort_order,
            'is_required' => $item->is_required,
        ])->toArray();
        $this->showItemsForm = true;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateType' => 'required|string|in:diagnostics,qc,intake,output',
            'templateStageId' => 'nullable|exists:pipeline_stages,id',
            'templateDeviceTypeId' => 'nullable|exists:device_types,id',
        ]);

        if ($this->editingTemplateId) {
            $template = ChecklistTemplate::findOrFail($this->editingTemplateId);
            $template->update([
                'name' => $this->templateName,
                'type' => $this->templateType,
                'stage_id' => $this->templateStageId,
                'device_type_id' => $this->templateDeviceTypeId,
                'description' => $this->templateDescription,
                'is_active' => $this->templateIsActive,
            ]);
        } else {
            ChecklistTemplate::create([
                'name' => $this->templateName,
                'type' => $this->templateType,
                'stage_id' => $this->templateStageId,
                'device_type_id' => $this->templateDeviceTypeId,
                'description' => $this->templateDescription,
                'is_active' => $this->templateIsActive,
            ]);
        }

        $this->dispatch('template-saved');
        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function addNewItem()
    {
        $this->validate([
            'newItemQuestion' => 'required|string|max:500',
        ]);

        $this->items[] = [
            'question' => $this->newItemQuestion,
            'field_type' => $this->newItemType,
            'options_json' => $this->newItemType === 'select' && $this->newItemOptions ? json_encode(explode(',', $this->newItemOptions)) : null,
            'sort_order' => count($this->items),
            'is_required' => true,
        ];

        $this->newItemQuestion = '';
        $this->newItemOptions = '';
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function saveItems()
    {
        if (! $this->editingTemplateId) {
            return;
        }

        ChecklistTemplateItem::where('template_id', $this->editingTemplateId)->delete();

        foreach ($this->items as $index => $itemData) {
            ChecklistTemplateItem::create([
                'template_id' => $this->editingTemplateId,
                'question' => $itemData['question'],
                'field_type' => $itemData['field_type'],
                'options_json' => is_array($itemData['options_json']) ? json_encode($itemData['options_json']) : $itemData['options_json'],
                'sort_order' => $index,
                'is_required' => $itemData['is_required'] ?? true,
            ]);
        }

        $this->dispatch('items-saved');
        $this->showItemsForm = false;
        $this->resetForm();
    }

    public function deleteTemplate($id)
    {
        ChecklistTemplate::destroy($id);
        $this->dispatch('template-deleted');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->showItemsForm = false;
    }

    public function render()
    {
        $templates = ChecklistTemplate::with(['stage', 'deviceType', 'items'])->orderBy('sort_order')->get();
        $stages = PipelineStage::all();
        $deviceTypes = DeviceType::all();
        return view('livewire.settings.checklist-templates', compact('templates', 'stages', 'deviceTypes'));
    }
}
```

---

## app/Http/

### app/Http/Controllers/Controller.php
```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
```

---

## app/Providers/

### app/Providers/AppServiceProvider.php
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
```

---

## routes/

### routes/web.php
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

### routes/console.php
```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
```

---

## bootstrap/

### bootstrap/app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## database/migrations/

### database/migrations/0001_01_01_000000_create_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

### database/migrations/2026_03_28_094752_create_ticketing_tables.php
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

### database/migrations/2026_03_28_085921_create_wms_tables_full.php
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

## resources/views/

### resources/views/components/layouts/app.blade.php
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ERP System' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 20px; }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1; }
        body { background-color: #f9fafb; }
        .sidebar { background: #ffffff; border-right: 1px solid #e5e7eb; }
        .sidebar-item { @apply flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors mb-0.5; }
        .sidebar-item.active { @apply bg-gray-900 text-white; }
        .sidebar-item:not(.active) { @apply text-gray-600 hover:bg-gray-100; }
    </style>
</head>
<body class="antialiased min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 fixed h-screen left-0 top-0 flex flex-col z-50">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-900 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">ERP</span>
                    </div>
                    <div>
                        <h1 class="text-sm font-semibold text-gray-900">ERP System</h1>
                        <p class="text-xs text-gray-500">Management Panel</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-3 overflow-y-auto">
                <a href="{{ route('dashboard') }}" wire:navigate class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <!-- Остальные пункты меню -->
            </nav>
            <div class="p-3 border-t border-gray-200">
                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-medium">
                            {{ mb_substr(auth()->user()?->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">{{ auth()->user()?->name ?? 'User' }}</p>
                            <p class="text-[10px] text-gray-500 truncate">{{ auth()->user()?->roles->first()?->name ?? 'User' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors">
                            <span class="material-symbols-outlined text-sm">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="ml-64 flex-1 min-h-screen">
            <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-40">
                <div class="flex items-center justify-between">
                    <nav class="flex items-center space-x-2 text-sm">
                        <span class="text-gray-400">Home</span>
                        <span class="material-symbols-outlined text-xs text-gray-300">chevron_right</span>
                        <span class="text-gray-900 font-medium">{{ $title ?? 'Dashboard' }}</span>
                    </nav>
                </div>
            </header>
            <div class="p-6">{{ $slot }}</div>
        </main>
    </div>
</body>
</html>
```

### resources/views/pdf/invoice.blade.php
```blade
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .total { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; }
        .signatures { margin-top: 50px; width: 100%; }
        .signatures td { width: 50%; text-align: center; padding-top: 40px; }
        .line { border-top: 1px solid #333; width: 80%; margin: 0 auto; margin-top: 5px; }
    </style>
</head>
<body>
<div class="header">
    <div class="title">Акт выполненных работ № {{ $ticket->id }}</div>
    <div>от {{ now()->format('d.m.Y') }}</div>
</div>

<table class="details">
    <tr>
        <td width="50%">
            <strong>Исполнитель:</strong> Сервисный центр "{{ $ticket->branch->name ?? 'Новые Решения' }}"<br>
            <strong>Устройство:</strong> {{ $ticket->device_brand }} {{ $ticket->device_model }}<br>
            <strong>S/N:</strong> {{ $ticket->serial_number ?? 'Б/Н' }}
        </td>
        <td width="50%">
            <strong>Заказчик:</strong> {{ $ticket->customer->name }}<br>
            <strong>Телефон:</strong> {{ $ticket->customer->phone }}<br>
            <strong>Дефект:</strong> {{ $ticket->defect_description }}
        </td>
    </tr>
</table>

<table class="table">
    <thead>
    <tr>
        <th>№</th>
        <th>Наименование работ / Запчастей</th>
        <th>Гарантия</th>
        <th>Сумма (₽)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ticket->usedParts as $index => $part)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $part->inventoryItem->product->name ?? 'Деталь' }} (S/N: {{ $part->inventoryItem->serial_number ?? 'Б/Н' }})</td>
            <td>{{ $part->warranty_days }} дн.</td>
            <td>{{ number_format($part->selling_price, 2, '.', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="total">ИТОГО К ОПЛАТЕ: {{ number_format($ticket->estimated_cost, 2, '.', ' ') }} ₽</div>

<table class="signatures">
    <tr>
        <td><div>Исполнитель</div><div class="line"></div></td>
        <td><div>Заказчик</div><div class="line"></div></td>
    </tr>
</table>

<div class="footer">Сгенерировано в ERP System "Новые Решения" • ID: {{ $ticket->ulid }}</div>
</body>
</html>
```

### resources/views/emails/magic-link.blade.php
```blade
<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
    <h2 style="color: #4f46e5; text-transform: uppercase;">Сервисный центр "Новые Решения"</h2>
    <p>Здравствуйте, <b>{{ $ticket->customer->name }}</b>!</p>
    <p>Ваше устройство <b>{{ $ticket->device_brand }} {{ $ticket->device_model }}</b> успешно прошло диагностику.</p>
    <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0; font-size: 12px; color: #6b7280; text-transform: uppercase;">Предварительная стоимость:</p>
        <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #111827;">{{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽</h3>
    </div>
    <p>Пожалуйста, перейдите по безопасной ссылке ниже:</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('client.portal', $link->token) }}" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; text-transform: uppercase;">Согласовать ремонт</a>
    </div>
</div>
```

### resources/views/livewire/auth/login.blade.php
```blade
<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative p-8">
    <div class="h-2 w-full bg-gradient-to-r from-gray-900 to-indigo-600 absolute top-0 left-0"></div>
    <div class="text-center mb-8 mt-4">
        <h2 class="text-2xl font-black italic tracking-tighter text-gray-900 uppercase">Вход в ERP</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Авторизация сотрудника</p>
    </div>
    <form wire:submit="login" class="space-y-6">
        <div>
            <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 bg-gray-50" placeholder="admin@example.com">
            @error('email') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Пароль</label>
            <input type="password" wire:model="password" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 bg-gray-50">
        </div>
        <div class="flex items-center">
            <input type="checkbox" wire:model="remember" id="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-xs font-bold text-gray-700">Запомнить меня</label>
        </div>
        <button type="submit" class="w-full py-4 bg-gray-900 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-black transition-all">
            <span wire:loading.remove wire:target="login">Войти в систему</span>
            <span wire:loading wire:target="login">Проверка...</span>
        </button>
    </form>
</div>
```

### resources/views/components/layouts/guest.blade.php
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Статус ремонта' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-gray-50 flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white shadow-xl mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h1 class="text-2xl font-black uppercase tracking-tighter italic text-gray-900">Новые Решения</h1>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Сервисный центр</p>
    </div>
    {{ $slot }}
    <div class="text-center mt-8 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        © {{ date('Y') }} ERP System. Все права защищены.
    </div>
</div>
</body>
</html>
```

---

## resources/js/

### resources/js/app.js
```javascript
import './bootstrap';
```

### resources/js/bootstrap.js
```javascript
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

---

## resources/css/

### resources/css/app.css
```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
}
```

---

## Архитектура проекта

### Основные модули:

1. **Branch (Филиалы)** - Управление филиалами компании и сотрудниками
2. **Customer (Клиенты)** - CRM система с поддержкой физ. и юр. лиц, интеграция DaData
3. **Finance (Финансы)** - Учёт доходов и расходов, P&L отчёты
4. **Inventory (Склад)** - WMS система с управлением складами и ячейками
5. **Ticketing (Заявки)** - Система управления ремонтами с канбан-доской, чек-листами, SLA

### Ключевые особенности:

- **Мультифилиальность** - изоляция данных по филиалам через `BranchIsolationScope`
- **Ролевая модель** - Admin, Branch Manager, Technician, Storekeeper
- **SLA мониторинг** - отслеживание времени на каждом этапе ремонта
- **Чек-листы** - шаблоны проверок для разных этапов и типов устройств
- **Magic Link** - согласование ремонта клиентами через email без регистрации
- **Интеграции** - DaData (проверка контрагентов), GSMArena (характеристики)
- **PDF-счета** - автоматическая генерация актов выполненных работ
- **Финансовая автоматизация** - автоматическое создание транзакций при закрытии заявок

### Технологический стек:

- **Backend:** Laravel 13, PHP 8.4
- **Frontend:** Livewire 4, TailwindCSS 4, Vite 8, Alpine.js
- **БД:** SQLite (настраивается на MySQL/PostgreSQL)
- **Пакеты:** Spatie Laravel Data, Spatie Laravel Permission, DomPDF, Laravel Pulse
- **Первичные ключи:** ULID

---

**Документ сгенерирован:** 2026-04-01  
**Всего файлов:** ~60 основных файлов приложения
