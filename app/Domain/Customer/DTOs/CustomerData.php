<?php

namespace App\Domain\Customer\DTOs;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $type, // individual, legal
        public string $name,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $inn = null,
        public ?string $kpp = null,
        public ?string $legal_address = null,
        public ?int $loyalty_level_id = null,
        public int $bonus_balance = 0,
        public ?array $meta = null,
    ) {}
}
