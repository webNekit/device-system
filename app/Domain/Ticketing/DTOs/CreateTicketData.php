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
