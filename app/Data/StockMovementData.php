<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class StockMovementData extends Data
{
    public function __construct(
        #[Required, IntegerType, Exists('products', 'id')]
        public int $product_id,
        #[Required, In(['in', 'out', 'adjustment'])]
        public string $type,
        #[Required, IntegerType, Min(1)]
        public int $quantity,
        public ?int $reference_id = null,
        public ?string $reference_type = null,
        #[Max(1000)]
        public ?string $notes = null,
        public ?int $user_id = null,
    ) {}
}
