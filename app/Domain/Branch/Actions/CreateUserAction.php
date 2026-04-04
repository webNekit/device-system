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
