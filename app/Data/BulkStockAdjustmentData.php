<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BulkStockAdjustmentData extends Data
{
    public function __construct(
        #[Required, ArrayType, Min(1)]
        public array $adjustments,
        #[Max(1000)]
        public ?string $notes = null,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'adjustments.*.product_id' => ['required', 'exists:products,id'],
            'adjustments.*.new_stock' => ['required', 'integer'],
        ];
    }
}
