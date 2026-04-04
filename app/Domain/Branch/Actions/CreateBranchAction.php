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
