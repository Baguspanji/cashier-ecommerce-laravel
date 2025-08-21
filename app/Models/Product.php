<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'barcode',
        'description',
        'price',
        'price_purchase',
        'image_path',
        'category_id',
        'current_stock',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_purchase' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the transaction items for the product.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    /**
     * Generate a unique barcode for the product.
     */
    public function generateBarcode(): string
    {
        // Generate EAN-13 compatible barcode based on product ID
        $prefix = '200'; // Internal product prefix
        $productId = str_pad((string) $this->id, 9, '0', STR_PAD_LEFT);
        $code = $prefix.$productId;

        // Calculate check digit for EAN-13
        $checkDigit = $this->calculateEan13CheckDigit($code);

        return $code.$checkDigit;
    }

    /**
     * Calculate EAN-13 check digit.
     */
    private function calculateEan13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $code[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Find product by barcode.
     */
    public static function findByBarcode(string $barcode): ?self
    {
        return static::where('barcode', $barcode)->where('is_active', true)->first();
    }
}
