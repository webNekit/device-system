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
