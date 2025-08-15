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
        #[Required, IntegerType]
        public int $user_id,
        #[Required, Numeric, Min(0)]
        public float $total_amount,
        #[Required, In(['cash', 'debit', 'credit', 'e-wallet'])]
        public string $payment_method,
        #[Required, Numeric, Min(0)]
        public float $payment_amount,
        #[Numeric, Min(0)]
        public float $change_amount = 0.0,
        #[In(['pending', 'completed', 'cancelled'])]
        public string $status = 'completed',
        #[Max(1000)]
        public ?string $notes = null,
        public ?string $transaction_number = null,
    ) {}
}
