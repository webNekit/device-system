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
            RoleSeeder::class,             // 1. Сначала роли (spatie/permission)
            UserRoleSeeder::class,         // 2. Филиалы и сотрудники
            DeviceDictionarySeeder::class, // 3. Справочник устройств (типы, бренды, модели)
            CustomerSeeder::class,         // 4. Клиенты и уровни лояльности
            InventorySeeder::class,        // 5. Склады, локации, товары, остатки
            ChecklistSeeder::class,        // 6. Чек-листы для этапов
            ChecklistTemplatesSeeder::class,// 7. Шаблоны чек-листов
            TicketingSeeder::class,        // 8. Воронки, стадии и заявки (канбан)
            FinancialSeeder::class,        // 9. Финансовые транзакции
        ]);
    }
}
