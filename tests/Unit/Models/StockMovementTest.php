<?php

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('stock movement can be created with valid data', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $stockMovement = StockMovement::create([
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 10,
        'previous_stock' => 50,
        'new_stock' => 60,
        'user_id' => $user->id,
    ]);

    expect($stockMovement)
        ->toBeInstanceOf(StockMovement::class)
        ->and($stockMovement->product_id)->toBe($product->id)
        ->and($stockMovement->type)->toBe('in')
        ->and($stockMovement->quantity)->toBe(10)
        ->and($stockMovement->previous_stock)->toBe(50)
        ->and($stockMovement->new_stock)->toBe(60);
});

test('stock movement belongs to product', function () {
    $stockMovement = StockMovement::factory()->create();

    expect($stockMovement->product)
        ->toBeInstanceOf(Product::class)
        ->and($stockMovement->product_id)->toBe($stockMovement->product->id);
});

test('stock movement belongs to user', function () {
    $stockMovement = StockMovement::factory()->create();

    expect($stockMovement->user)
        ->toBeInstanceOf(User::class)
        ->and($stockMovement->user_id)->toBe($stockMovement->user->id);
});

test('stock movement can create movement and update product stock for in type', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 50]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'in',
        10,
        notes: 'Stock replenishment'
    );

    $product->refresh();

    expect($stockMovement)
        ->toBeInstanceOf(StockMovement::class)
        ->and($stockMovement->type)->toBe('in')
        ->and($stockMovement->quantity)->toBe(10)
        ->and($stockMovement->previous_stock)->toBe(50)
        ->and($stockMovement->new_stock)->toBe(60)
        ->and($product->current_stock)->toBe(60);
});

test('stock movement can create movement and update product stock for out type', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 50]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'out',
        15,
        notes: 'Sale'
    );

    $product->refresh();

    expect($stockMovement)
        ->toBeInstanceOf(StockMovement::class)
        ->and($stockMovement->type)->toBe('out')
        ->and($stockMovement->quantity)->toBe(15)
        ->and($stockMovement->previous_stock)->toBe(50)
        ->and($stockMovement->new_stock)->toBe(35)
        ->and($product->current_stock)->toBe(35);
});

test('stock movement allows negative stock', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 5]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'out',
        10
    );

    $product->refresh();

    expect($stockMovement->new_stock)->toBe(-5)
        ->and($product->current_stock)->toBe(-5);
});

test('stock movement factory creates valid movement', function () {
    $stockMovement = StockMovement::factory()->create();

    expect($stockMovement)
        ->toBeInstanceOf(StockMovement::class)
        ->and($stockMovement->type)->toBeString()
        ->and($stockMovement->quantity)->toBeInt()
        ->and($stockMovement->previous_stock)->toBeInt()
        ->and($stockMovement->new_stock)->toBeInt();
});

test('stock movement returns correct relationship instances', function () {
    $stockMovement = StockMovement::factory()->create();

    expect($stockMovement->product())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
        ->and($stockMovement->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

test('stock movement can handle adjustment type', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 50]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'adjustment',
        5,
        notes: 'Stock adjustment'
    );

    $product->refresh();

    expect($stockMovement->type)->toBe('adjustment')
        ->and($stockMovement->new_stock)->toBe(55)
        ->and($product->current_stock)->toBe(55);
});

test('stock movement can include reference details', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 50]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'out',
        10,
        referenceId: 123,
        referenceType: 'transaction',
        notes: 'Sale transaction'
    );

    expect($stockMovement->reference_id)->toBe(123)
        ->and($stockMovement->reference_type)->toBe('transaction')
        ->and($stockMovement->notes)->toBe('Sale transaction');
});

test('stock movement uses authenticated user when no user provided', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $product = Product::factory()->create(['current_stock' => 50]);

    $stockMovement = StockMovement::createMovement(
        $product,
        'in',
        10
    );

    expect($stockMovement->user_id)->toBe($user->id);
});
