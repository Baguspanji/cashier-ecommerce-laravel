<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class TransactionItemData extends Data
{
    public function __construct(
        public ?int $id,
        #[IntegerType]
        public ?int $transaction_id,
        #[Required, IntegerType]
        public int $product_id,
        #[Required, StringType]
        public string $product_name,
        #[Required, IntegerType, Min(1)]
        public int $quantity,
        #[Required, Numeric, Min(0)]
        public float $unit_price,
        #[Numeric, Min(0)]
        public float $price_purchase,
        #[Required, Numeric, Min(0)]
        public float $subtotal,
        public ?ProductData $product = null,
    ) {}
}
