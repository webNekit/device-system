<?php

namespace App\Domain\Branch\DTOs;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class BranchData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|string|max:255')]
        public string $address,

        #[Rule('required|timezone')]
        public string $timezone = 'Europe/Moscow',

        #[Rule('nullable|array')]
        public ?array $settings = null,
    ) {}
}
