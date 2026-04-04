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
