<?php

namespace App\Domain\Branch\DTOs;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|email|unique:users,email')]
        public string $email,

        #[Rule('required|string|min:8')]
        public string $password,

        #[Rule('nullable|exists:branches,id')]
        public ?int $branch_id = null,

        #[Rule('nullable|string|exists:roles,name')]
        public ?string $role_name = null,
    ) {}
}
