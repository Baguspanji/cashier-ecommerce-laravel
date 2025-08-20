<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_id',
        'reference_type',
        'notes',
        'user_id',
        'offline_id',
        'sync_status',
        'last_sync_at',
        'sync_metadata',
    ];

    protected function casts(): array
    {
        return [
            'sync_metadata' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    /**
     * Get the product that owns the stock movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the stock movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a stock movement record and update product stock.
     */
    public static function createMovement(
        Product $product,
        string $type,
        int $quantity,
        ?int $referenceId = null,
        ?string $referenceType = null,
        ?string $notes = null,
        ?string $offlineId = null,
        ?string $syncStatus = null,
        ?string $lastSyncAt = null,
        ?int $userId = null
    ): self {
        $previousStock = $product->current_stock;

        // Calculate new stock based on movement type
        $newStock = match ($type) {
            'in', 'adjustment' => $previousStock + $quantity,
            'out' => $previousStock - $quantity,
            default => $previousStock,
        };

        // Update product stock
        $product->update(['current_stock' => max(0, $newStock)]);

        // Create movement record
        return static::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => max(0, $newStock),
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'notes' => $notes,
            'offline_id' => $offlineId,
            'sync_status' => $syncStatus,
            'last_sync_at' => $lastSyncAt,
            'user_id' => $userId ?? Auth::id(),
        ]);
    }
}
