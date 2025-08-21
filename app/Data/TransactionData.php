<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    public function __construct(
        public ?int $id,
        public int $user_id,
        public float $total_amount,
        public float $income,
        public string $payment_method,
        public float $payment_amount,
        public float $change_amount = 0.0,
        public string $status = 'completed',
        public ?string $notes = null,
        public ?string $transaction_number = null,
        public string $created_at,
        public string $updated_at,
        /** @var TransactionItemData[] */
        public array $items = [],
        public ?UserData $user = null,
    ) {}
}
