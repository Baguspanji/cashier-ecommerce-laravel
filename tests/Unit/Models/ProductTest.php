<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product can be created with valid data', function () {
    $category = Category::factory()->create();

    $product = Product::create([
        'name' => 'Test Product',
        'description' => 'Test description',
        'price' => 25000,
        'category_id' => $category->id,
        'current_stock' => 50,
        'minimum_stock' => 10,
        'is_active' => true,
    ]);

    expect($product)
        ->toBeInstanceOf(Product::class)
        ->and($product->name)->toBe('Test Product')
        ->and($product->price)->toBe('25000.00')
        ->and($product->current_stock)->toBe(50)
        ->and($product->minimum_stock)->toBe(10)
        ->and($product->is_active)->toBeTrue();
});

test('product belongs to category', function () {
    $product = Product::factory()->create();

    expect($product->category)
        ->toBeInstanceOf(Category::class)
        ->and($product->category_id)->toBe($product->category->id);
});

test('product can check if low stock', function () {
    $product = Product::factory()->create([
        'current_stock' => 3,
        'minimum_stock' => 5,
    ]);

    expect($product->isLowStock())->toBeTrue();

    $product->update(['current_stock' => 10]);
    expect($product->isLowStock())->toBeFalse();
});

test('product can check if out of stock', function () {
    $product = Product::factory()->create(['current_stock' => 0]);
    expect($product->isOutOfStock())->toBeTrue();

    $product->update(['current_stock' => 1]);
    expect($product->isOutOfStock())->toBeFalse();
});

test('product has transaction items relationship', function () {
    $product = Product::factory()->create();
    TransactionItem::factory(2)->create(['product_id' => $product->id]);

    expect($product->transactionItems)->toHaveCount(2)
        ->and($product->transactionItems->first())->toBeInstanceOf(TransactionItem::class);
});

test('product has stock movements relationship', function () {
    $product = Product::factory()->create();
    StockMovement::factory(3)->create(['product_id' => $product->id]);

    expect($product->stockMovements)->toHaveCount(3)
        ->and($product->stockMovements->first())->toBeInstanceOf(StockMovement::class);
});

test('product factory creates valid product', function () {
    $product = Product::factory()->create();

    expect($product)
        ->toBeInstanceOf(Product::class)
        ->and($product->name)->toBeString()
        ->and($product->price)->toBeNumeric()
        ->and($product->current_stock)->toBeInt()
        ->and($product->minimum_stock)->toBeInt()
        ->and($product->is_active)->toBeBool();
});

test('product price is cast to decimal', function () {
    $product = Product::factory()->create(['price' => 15000]);

    expect($product->price)->toBe('15000.00');
});

test('product is_active is cast to boolean', function () {
    $product = Product::factory()->create(['is_active' => 1]);

    expect($product->is_active)->toBeTrue();
});

test('product returns correct relationship instances', function () {
    $product = Product::factory()->create();

    expect($product->category())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
        ->and($product->transactionItems())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($product->stockMovements())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('product with zero stock is out of stock', function () {
    $product = Product::factory()->create(['current_stock' => 0]);

    expect($product->isOutOfStock())->toBeTrue();
});

test('product with stock equal to minimum is low stock', function () {
    $product = Product::factory()->create([
        'current_stock' => 5,
        'minimum_stock' => 5,
    ]);

    expect($product->isLowStock())->toBeTrue();
});
