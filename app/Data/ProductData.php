<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public float $price,
        public ?string $image_path,
        public int $category_id,
        public int $current_stock,
        public int $minimum_stock,
        public bool $is_active = true,
    ) {}
}
