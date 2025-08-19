<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\PaymentMethod;
use App\UserRole;

// Basic Transaction Tests
it('can create a transaction through POS', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
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
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 35000,
        'notes' => 'Test transaction from POS',
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'total_amount' => 30000, // 2 * 15000
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 35000,
        'change_amount' => 5000,
        'status' => 'completed',
        'notes' => 'Test transaction from POS',
    ]);

    $this->assertDatabaseHas('transaction_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    // Check stock reduction
    $product->refresh();
    $this->assertEquals(8, $product->current_stock); // 10 - 2
});

it('can create a transaction with multiple items', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin
    ]);
    $category = Category::factory()->create();
    $product1 = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1111111111111',
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);
    $product2 = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '2222222222222',
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
        'payment_method' => PaymentMethod::DebitCard->value,
        'payment_amount' => 55000,
        'notes' => 'Multi-item transaction',
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'total_amount' => 55000, // (2 * 15000) + (1 * 25000)
        'payment_method' => PaymentMethod::DebitCard->value,
        'payment_amount' => 55000,
        'change_amount' => 0,
        'status' => 'completed',
        'notes' => 'Multi-item transaction',
    ]);

    $transaction = Transaction::latest()->first();
    $this->assertCount(2, $transaction->items);

    // Check individual transaction items
    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $product1->id,
        'product_name' => $product1->name,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $product2->id,
        'product_name' => $product2->name,
        'quantity' => 1,
        'unit_price' => 25000,
        'subtotal' => 25000,
    ]);

    // Check stock updates and movements
    $product1->refresh();
    $product2->refresh();
    $this->assertEquals(8, $product1->current_stock); // 10 - 2
    $this->assertEquals(4, $product2->current_stock); // 5 - 1

    // Check stock movements created
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product1->id,
        'type' => 'out',
        'quantity' => 2,
        'reference_type' => 'transaction',
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product2->id,
        'type' => 'out',
        'quantity' => 1,
        'reference_type' => 'transaction',
    ]);
});

// POS Interface Tests
it('can access POS page with products including barcode', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'is_active' => true,
        'current_stock' => 5,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Transactions/POS')
        ->has('products')
        ->has('products.0.barcode')
    );
});

it('shows only active products with stock in POS', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();

    // Active product with stock
    $activeProduct = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1111111111111',
        'is_active' => true,
        'current_stock' => 5,
    ]);

    // Inactive product
    $inactiveProduct = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '2222222222222',
        'is_active' => false,
        'current_stock' => 10,
    ]);

    // Out of stock product
    $outOfStockProduct = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '3333333333333',
        'is_active' => true,
        'current_stock' => 0,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Transactions/POS')
        ->has('products', 1) // Only active product with stock
        ->where('products.0.id', $activeProduct->id)
    );
});

// Barcode Search Tests
it('can search product by barcode', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'is_active' => true,
        'current_stock' => 5,
    ]);

    $response = $this->actingAs($user)
        ->get(route('products.search-barcode', ['barcode' => '1234567890123']));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => '1234567890123',
            ]
        ]);
});

it('returns 404 when barcode not found', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin
    ]);

    $response = $this->actingAs($user)
        ->get(route('products.search-barcode', ['barcode' => 'nonexistent']));

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Product not found'
        ]);
});

it('only returns active products in barcode search', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'is_active' => false, // Inactive product
        'current_stock' => 5,
    ]);

    $response = $this->actingAs($user)
        ->get(route('products.search-barcode', ['barcode' => '1234567890123']));

    $response->assertNotFound();
});

// Transaction Validation Tests
it('validates insufficient payment amount', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
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
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 20000, // Less than total (30000)
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertServerError();
    $this->assertDatabaseMissing('transactions', [
        'user_id' => $user->id,
    ]);

    // Stock should not be affected
    $product->refresh();
    $this->assertEquals(10, $product->current_stock);
});

it('validates insufficient stock', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
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
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 100000,
    ];

    $response = $this->actingAs($user)
        ->postJson(route('transactions.store'), $transactionData);

    $response->assertServerError();
    $this->assertDatabaseMissing('transactions', [
        'user_id' => $user->id,
    ]);

    // Stock should not be affected
    $product->refresh();
    $this->assertEquals(1, $product->current_stock);
});

// Transaction Display Tests
it('can view transaction details', function () {
    $user = User::factory()->create([
        'role' => UserRole::Manager
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'total_amount' => 30000,
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 50000,
        'change_amount' => 20000,
    ]);

    TransactionItem::factory()->create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    $response = $this->actingAs($user)
        ->get(route('transactions.show', $transaction));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Transactions/Show')
            ->has('transaction')
            ->where('transaction.id', $transaction->id)
            ->where('transaction.total_amount', 30000)
        );
});

// Transaction Cancellation Tests
it('can cancel a completed transaction', function () {
    $user = User::factory()->create([
        'role' => UserRole::Manager
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'current_stock' => 5, // Reduced from initial stock
        'price' => 15000,
        'is_active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'total_amount' => 30000,
    ]);

    TransactionItem::factory()->create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('transactions.cancel', $transaction));

    $response->assertRedirect();

    $transaction->refresh();
    $this->assertEquals('cancelled', $transaction->status);

    // Check stock restored
    $product->refresh();
    $this->assertEquals(7, $product->current_stock); // 5 + 2

    // Check cancellation stock movement created
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 2,
        'reference_type' => 'cancellation',
        'reference_id' => $transaction->id,
    ]);
});

// Payment Method Tests
it('supports various payment methods', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);

    $paymentMethods = [
        PaymentMethod::Cash->value,
        PaymentMethod::DebitCard->value,
        PaymentMethod::CreditCard->value,
        PaymentMethod::EWallet->value,
        PaymentMethod::QRIS->value
    ];

    foreach ($paymentMethods as $method) {
        $transactionData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => $method,
            'payment_amount' => 20000,
        ];

        $response = $this->actingAs($user)
            ->postJson(route('transactions.store'), $transactionData);

        $response->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'payment_method' => $method,
            'total_amount' => 15000,
        ]);
    }
});

// Transaction Number Generation Test
it('generates unique transaction numbers', function () {
    $user = User::factory()->create([
        'role' => UserRole::Cashier
    ]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'barcode' => '1234567890123',
        'current_stock' => 10,
        'price' => 15000,
        'is_active' => true,
    ]);

    $transactionData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
            ],
        ],
        'payment_method' => PaymentMethod::Cash->value,
        'payment_amount' => 20000,
    ];

    // Create multiple transactions
    for ($i = 0; $i < 3; $i++) {
        $response = $this->actingAs($user)
            ->postJson(route('transactions.store'), $transactionData);
        $response->assertRedirect();
    }

    $transactions = Transaction::all();
    $transactionNumbers = $transactions->pluck('transaction_number')->toArray();

    // All transaction numbers should be unique
    $this->assertEquals(count($transactionNumbers), count(array_unique($transactionNumbers)));

    // Transaction numbers should follow the expected format
    foreach ($transactionNumbers as $number) {
        $this->assertMatchesRegularExpression('/^TRX\d{8}\d{4}$/', $number);
    }
});
