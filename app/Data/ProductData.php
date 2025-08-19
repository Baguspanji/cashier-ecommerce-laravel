<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class ProductData extends Data
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public string $price,
        public ?string $image_path,
        public int $category_id,
        public int $current_stock,
        public int $minimum_stock,
        public bool $is_active,
        public ?string $created_at,
        public ?string $updated_at,
        public ?CategoryData $category = null,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public static function attributes(...$args)
    {
        return [
            'name' => 'Nama Produk',
            'description' => 'Deskripsi',
            'price' => 'Harga',
            'image_path' => 'Path Gambar',
            'category_id' => 'Kategori',
            'current_stock' => 'Stok Saat Ini',
            'minimum_stock' => 'Stok Minimum',
            'is_active' => 'Status Aktif',
        ];
    }
}
