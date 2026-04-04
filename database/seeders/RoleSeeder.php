<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin',            // Полный доступ
            'Branch Manager',   // Управляющий филиалом
            'Technician',       // Мастер по ремонту
            'Storekeeper',      // Кладовщик
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
