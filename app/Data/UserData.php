<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public ?int $id,
        #[Required, StringType]
        public string $name,
        #[Required, Email]
        public string $email,
        public ?string $email_verified_at = null,
        /** @var TransactionData[] */
        public array $transactions = [],
        /** @var StockMovementData[] */
        public array $stock_movements = [],
    ) {}
}
