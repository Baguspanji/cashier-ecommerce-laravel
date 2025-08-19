<?php

namespace App\Data;

use App\UserRole;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UserData extends Data
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public ?string $password = null,
        public ?string $email_verified_at = null,
        public UserRole $role = UserRole::Cashier,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        /** @var TransactionData[] */
        public array $transactions = [],
        /** @var StockMovementData[] */
        public array $stock_movements = [],
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        $userId = $context?->payload['id'] ?? null;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', $userId ? "unique:users,email,{$userId}" : 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public static function attributes(...$args)
    {
        return [
            'name' => 'Nama Pengguna',
            'email' => 'Email',
            'role' => 'Peran',
        ];
    }
}
