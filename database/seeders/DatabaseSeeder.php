<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Главный метод запуска всех сидеров.
     * Порядок ОЧЕНЬ ВАЖЕН, так как таблицы зависят друг от друга.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // 1. Сначала роли
            UserRoleSeeder::class,    // 2. Затем филиалы и сотрудники
            CustomerSeeder::class,    // 3. Затем клиенты
            InventorySeeder::class,   // 4. Затем склады и номенклатура
            ChecklistSeeder::class,   // 5. Чек-листы для этапов
            ChecklistTemplatesSeeder::class,   // 6. Шаблоны чек-листов
            TicketingSeeder::class,   // 7. И только в конце — воронки и заявки
        ]);
    }
}
