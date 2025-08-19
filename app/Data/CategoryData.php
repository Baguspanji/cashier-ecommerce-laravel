<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class CategoryData extends Data
{
    public function __construct(
        public ?int $id,
        #[Required, StringType]
        public string $name,
        public ?string $description = null,
        public ?int $products_count = null
    ) {}

    public static function attributes(...$args): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nama Kategori',
            'description' => 'Deskripsi',
        ];
    }

}
