<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateTransactionData extends Data
{
    public function __construct(
        #[Required, ArrayType, Min(1)]
        public array $items,
        #[Required, In(['cash', 'debit', 'credit', 'e-wallet'])]
        public string $payment_method,
        #[Required, Numeric, Min(0)]
        public float $payment_amount,
        #[Max(1000)]
        public ?string $notes = null,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
