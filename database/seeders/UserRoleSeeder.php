<?php

namespace Database\Seeders;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $branchMoscow = Branch::firstOrCreate(['name' => 'Филиал Москва'], ['address' => 'ул. Тверская, 1', 'timezone' => 'Europe/Moscow']);
        $branchPiter = Branch::firstOrCreate(['name' => 'Филиал Питер'], ['address' => 'Невский пр., 10', 'timezone' => 'Europe/Moscow']);

        $roles = ['Admin', 'Branch Manager', 'Technician', 'Storekeeper'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $password = Hash::make('password123');

        $admin = User::firstOrCreate(
            ['email' => 'admin@erp.ru'],
            ['name' => 'Главный Администратор', 'password' => $password, 'branch_id' => $branchMoscow->id]
        );
        $admin->assignRole('Admin');

        // --- МЕНЕДЖЕРЫ (Видят Заявки, Клиентов, Сотрудников своего филиала) ---
        $manager1 = User::firstOrCreate(
            ['email' => 'manager.msk@erp.ru'],
            ['name' => 'Менеджер Михаил (Москва)', 'password' => $password, 'branch_id' => $branchMoscow->id]
        );
        $manager1->assignRole('Branch Manager');

        $manager2 = User::firstOrCreate(
            ['email' => 'manager.spb@erp.ru'],
            ['name' => 'Менеджер Петр (Питер)', 'password' => $password, 'branch_id' => $branchPiter->id]
        );
        $manager2->assignRole('Branch Manager');

        // --- МАСТЕРА (Видят ТОЛЬКО Канбан-доску заявок своего филиала) ---
        $tech1 = User::firstOrCreate(
            ['email' => 'tech.msk@erp.ru'],
            ['name' => 'Мастер Иван (Москва)', 'password' => $password, 'branch_id' => $branchMoscow->id, 'commission_percent' => 40]
        );
        $tech1->assignRole('Technician');

        $tech2 = User::firstOrCreate(
            ['email' => 'tech.spb@erp.ru'],
            ['name' => 'Мастер Сергей (Питер)', 'password' => $password, 'branch_id' => $branchPiter->id, 'commission_percent' => 40]
        );
        $tech2->assignRole('Technician');

        // --- КЛАДОВЩИКИ (Видят ТОЛЬКО Склад своего филиала) ---
        $store1 = User::firstOrCreate(
            ['email' => 'store.msk@erp.ru'],
            ['name' => 'Кладовщик Олег (Москва)', 'password' => $password, 'branch_id' => $branchMoscow->id]
        );
        $store1->assignRole('Storekeeper');

        $store2 = User::firstOrCreate(
            ['email' => 'store.spb@erp.ru'],
            ['name' => 'Кладовщик Анна (Питер)', 'password' => $password, 'branch_id' => $branchPiter->id]
        );
        $store2->assignRole('Storekeeper');
    }
}
