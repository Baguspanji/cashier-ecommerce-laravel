<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transaction item can be created with valid data', function () {
    $transaction = Transaction::factory()->create();
    $product = Product::factory()->create();

    $transactionItem = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    expect($transactionItem)
        ->toBeInstanceOf(TransactionItem::class)
        ->and($transactionItem->transaction_id)->toBe($transaction->id)
        ->and($transactionItem->product_id)->toBe($product->id)
        ->and($transactionItem->quantity)->toBe(2)
        ->and($transactionItem->unit_price)->toBe('15000.00')
        ->and($transactionItem->subtotal)->toBe('30000.00');
});

test('transaction item belongs to transaction', function () {
    $transactionItem = TransactionItem::factory()->create();

    expect($transactionItem->transaction)
        ->toBeInstanceOf(Transaction::class)
        ->and($transactionItem->transaction_id)->toBe($transactionItem->transaction->id);
});

test('transaction item belongs to product', function () {
    $transactionItem = TransactionItem::factory()->create();

    expect($transactionItem->product)
        ->toBeInstanceOf(Product::class)
        ->and($transactionItem->product_id)->toBe($transactionItem->product->id);
});

test('transaction item factory creates valid item', function () {
    $transactionItem = TransactionItem::factory()->create();

    expect($transactionItem)
        ->toBeInstanceOf(TransactionItem::class)
        ->and($transactionItem->product_name)->toBeString()
        ->and($transactionItem->quantity)->toBeInt()
        ->and($transactionItem->unit_price)->toBeNumeric()
        ->and($transactionItem->subtotal)->toBeNumeric();
});

test('transaction item prices are cast to decimal', function () {
    $transactionItem = TransactionItem::factory()->create([
        'unit_price' => 25000,
        'subtotal' => 50000,
    ]);

    expect($transactionItem->unit_price)->toBe('25000.00')
        ->and($transactionItem->subtotal)->toBe('50000.00');
});

test('transaction item returns correct relationship instances', function () {
    $transactionItem = TransactionItem::factory()->create();

    expect($transactionItem->transaction())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
        ->and($transactionItem->product())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

test('transaction item stores product name for historical reference', function () {
    $product = Product::factory()->create(['name' => 'Original Product Name']);
    $transactionItem = TransactionItem::factory()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
    ]);

    // Even if product name changes, transaction item keeps original name
    $product->update(['name' => 'Updated Product Name']);

    expect($transactionItem->product_name)->toBe('Original Product Name')
        ->and($transactionItem->product->name)->toBe('Updated Product Name');
});

test('transaction item quantity must be positive', function () {
    $transactionItem = TransactionItem::factory()->create(['quantity' => 5]);

    expect($transactionItem->quantity)->toBeGreaterThan(0);
});
