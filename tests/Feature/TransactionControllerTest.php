<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

it('can create a transaction through POS', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);

    $transactionData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ],
        'payment_method' => 'cash',
        'payment_amount' => 50000,
        'notes' => 'Test transaction',
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'total_amount' => 30000, // 2 * 15000
        'payment_method' => 'cash',
        'payment_amount' => 50000,
        'change_amount' => 20000,
        'status' => 'completed',
        'notes' => 'Test transaction',
    ]);

    $transaction = Transaction::latest()->first();
    $this->assertCount(1, $transaction->items);

    $product->refresh();
    $this->assertEquals(8, $product->current_stock); // 10 - 2
});

it('can create a transaction with multiple items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product1 = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);
    $product2 = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 5,
        'price' => 25000,
        'is_active' => true,
    ]);

    $transactionData = [
        'items' => [
            [
                'product_id' => $product1->id,
                'quantity' => 2,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 1,
            ],
        ],
        'payment_method' => 'cash',
        'payment_amount' => 100000,
        'notes' => 'Multi-item transaction',
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'total_amount' => 55000, // (2 * 15000) + (1 * 25000)
        'payment_method' => 'cash',
        'payment_amount' => 100000,
        'change_amount' => 45000,
        'status' => 'completed',
        'notes' => 'Multi-item transaction',
    ]);

    $transaction = Transaction::latest()->first();
    $this->assertCount(2, $transaction->items);

    // Check individual transaction items
    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $product2->id,
        'quantity' => 1,
        'unit_price' => 25000,
        'subtotal' => 25000,
    ]);

    // Check stock updates
    $product1->refresh();
    $product2->refresh();
    $this->assertEquals(8, $product1->current_stock); // 10 - 2
    $this->assertEquals(4, $product2->current_stock); // 5 - 1
});

it('can access POS page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => true,
        'current_stock' => 5,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Transactions/POS')
        ->has('products')
    );
});

it('validates insufficient payment amount', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);

    $transactionData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ],
        'payment_method' => 'cash',
        'payment_amount' => 20000, // Less than total (30000)
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertServerError();
    $this->assertDatabaseMissing('transactions', [
        'user_id' => $user->id,
    ]);
});

it('validates insufficient stock', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 1,
        'price' => 15000,
        'is_active' => true,
    ]);

    $transactionData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 5, // More than available stock (1)
            ],
        ],
        'payment_method' => 'cash',
        'payment_amount' => 100000,
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertServerError();
    $this->assertDatabaseMissing('transactions', [
        'user_id' => $user->id,
    ]);
});
