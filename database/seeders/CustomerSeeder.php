<?php

namespace Database\Seeders;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\LoyaltyLevel;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $level = LoyaltyLevel::firstOrCreate([
            'name' => 'Базовый',
        ], [
            'discount_percent' => 0,
            'min_spend' => 0,
        ]);

        $vip = LoyaltyLevel::firstOrCreate([
            'name' => 'VIP',
        ], [
            'discount_percent' => 15,
            'min_spend' => 50000,
        ]);

        Customer::firstOrCreate(
            ['email' => 'ivanov@example.com'],
            [
                'name' => 'Иванов Иван',
                'phone' => '+79991234567',
                'loyalty_level_id' => $level->id,
                'bonus_balance' => 0,
                'type' => 'individual',
            ]
        );

        Customer::firstOrCreate(
            ['inn' => '7707083893'],
            [
                'name' => 'ООО "Ромашка"',
                'type' => 'legal',
                'email' => 'info@romashka.ru',
                'phone' => '+74950000000',
                'loyalty_level_id' => $vip->id,
                'bonus_balance' => 1500,
            ]
        );
    }
}
