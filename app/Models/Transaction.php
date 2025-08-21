<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'user_id',
        'customer_name',
        'total_amount',
        'payment_method',
        'payment_amount',
        'change_amount',
        'income',
        'status',
        'notes',
        'offline_id',
        'sync_status',
        'last_sync_at',
        'sync_metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'income' => 'decimal:2',
        'sync_metadata' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction items for the transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Generate a unique transaction number.
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return $prefix.$date.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
