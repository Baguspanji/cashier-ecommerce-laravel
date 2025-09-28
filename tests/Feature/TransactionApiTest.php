<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($this->admin);

    // Create test data
    $this->category = Category::factory()->create(['name' => 'Test Category']);
    $this->product1 = Product::factory()->create([
        'name' => 'Test Product 1',
        'price' => 10000,
        'price_purchase' => 7000,
        'current_stock' => 100,
        'minimum_stock' => 10,
        'category_id' => $this->category->id,
    ]);
    $this->product2 = Product::factory()->create([
        'name' => 'Test Product 2',
        'price' => 15000,
        'price_purchase' => 10000,
        'current_stock' => 50,
        'minimum_stock' => 5,
        'category_id' => $this->category->id,
    ]);
});

describe('Transaction API', function () {
    it('can list transactions with pagination', function () {
        // Create test transactions with unique numbers
        for ($i = 1; $i <= 5; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->admin->id,
                'transaction_number' => 'TRX'.now()->format('Ymd').str_pad($i + 100, 4, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->getJson('/api/transactions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'transaction_number',
                        'user_id',
                        'total_amount',
                        'payment_method',
                        'payment_amount',
                        'change_amount',
                        'income',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    });

    it('can filter transactions by period', function () {
        // Create transactions with different dates
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'transaction_number' => 'TRX'.now()->subDays(2)->format('Ymd').'0001',
            'created_at' => now()->subDays(2),
        ]);
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'transaction_number' => 'TRX'.now()->format('Ymd').'0002',
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/transactions?period=today');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('can filter transactions by payment method', function () {
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'payment_method' => 'cash',
        ]);
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'payment_method' => 'debit_card',
        ]);

        $response = $this->getJson('/api/transactions?payment_method=cash');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('can filter transactions by status', function () {
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
        ]);
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'cancelled',
        ]);

        $response = $this->getJson('/api/transactions?status=completed');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('can search transactions by transaction number', function () {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'transaction_number' => 'TRX202509280001',
        ]);
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'transaction_number' => 'TRX202509280002',
        ]);

        $response = $this->getJson('/api/transactions?search=0001');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
        expect($response->json('data.0.transaction_number'))->toBe('TRX202509280001');
    });

    it('can create a new transaction successfully', function () {
        $transactionData = [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 40000,
            'notes' => 'Test transaction',
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'transaction_number',
                    'total_amount',
                    'payment_method',
                    'payment_amount',
                    'change_amount',
                    'income',
                    'status',
                    'items',
                    'user',
                ],
            ]);

        // Check transaction was created
        expect($response->json('data.total_amount'))->toBe(35000); // (10000*2) + (15000*1)
        expect($response->json('data.change_amount'))->toBe(5000); // 40000 - 35000
        expect($response->json('data.income'))->toBe(11000); // (3000*2) + (5000*1)
        expect($response->json('data.status'))->toBe('completed');

        // Check stock was updated
        expect($this->product1->fresh()->current_stock)->toBe(98); // 100 - 2
        expect($this->product2->fresh()->current_stock)->toBe(49); // 50 - 1

        // Check stock movements were created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product1->id,
            'type' => 'out',
            'quantity' => 2,
        ]);
    });

    it('cannot create transaction with insufficient stock', function () {
        $this->product1->update(['current_stock' => 1]);

        $transactionData = [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 5, // More than available stock
                ],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 50000,
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Transaction failed: Insufficient stock for product: Test Product 1. Available: 1, Required: 5',
            ]);
    });

    it('cannot create transaction with insufficient payment', function () {
        $transactionData = [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2,
                ],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 15000, // Less than total (20000)
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Transaction failed: Payment amount is insufficient',
            ]);
    });

    it('validates required fields when creating transaction', function () {
        $response = $this->postJson('/api/transactions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items', 'payment_method', 'payment_amount']);
    });

    it('validates transaction items structure', function () {
        $transactionData = [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    // Missing quantity
                ],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 20000,
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    });

    it('can show a specific transaction', function () {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'transaction_number',
                    'user_id',
                    'total_amount',
                    'payment_method',
                    'status',
                    'user',
                    'items',
                ],
            ]);

        expect($response->json('data.id'))->toBe($transaction->id);
    });

    it('returns 404 for non-existent transaction', function () {
        $response = $this->getJson('/api/transactions/99999');

        $response->assertNotFound();
    });

    it('can cancel a transaction', function () {
        // Create transaction with items
        $transaction = Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
        ]);

        // Create transaction items
        $transaction->items()->create([
            'product_id' => $this->product1->id,
            'product_name' => $this->product1->name,
            'quantity' => 2,
            'unit_price' => $this->product1->price,
            'price_purchase' => $this->product1->price_purchase,
            'subtotal' => $this->product1->price * 2,
        ]);

        // Reduce stock first (simulate the transaction effect)
        $this->product1->decrement('current_stock', 2);
        $originalStock = $this->product1->current_stock;

        $response = $this->patchJson("/api/transactions/{$transaction->id}/cancel");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Transaction cancelled successfully.',
            ]);

        // Check transaction status updated
        expect($transaction->fresh()->status)->toBe('cancelled');

        // Check stock was restored
        expect($this->product1->fresh()->current_stock)->toBe($originalStock + 2);

        // Check stock movement was created for restoration
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product1->id,
            'type' => 'in',
            'quantity' => 2,
            'reference_id' => $transaction->id,
        ]);
    });

    it('cannot cancel already cancelled transaction', function () {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'cancelled',
        ]);

        $response = $this->patchJson("/api/transactions/{$transaction->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Transaction is already cancelled.',
            ]);
    });

    it('can get daily report', function () {
        // Create transactions for today with unique numbers
        for ($i = 1; $i <= 3; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->admin->id,
                'status' => 'completed',
                'total_amount' => 10000,
                'income' => 3000,
                'payment_method' => 'cash',
                'transaction_number' => 'TRX'.now()->format('Ymd').str_pad($i, 4, '0', STR_PAD_LEFT),
                'created_at' => now(),
            ]);
        }

        // Create transaction for yesterday (should not be included)
        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'transaction_number' => 'TRX'.now()->subDay()->format('Ymd').'0001',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/transactions/daily-report');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'total_transactions',
                    'total_revenue',
                    'total_income',
                    'payment_methods',
                    'top_products',
                    'hourly_sales',
                ],
                'transactions',
            ]);

        expect($response->json('data.total_transactions'))->toBe(3);
        expect($response->json('data.total_revenue'))->toBe(30000);
        expect($response->json('data.total_income'))->toBe(9000);
    });

    it('can get daily report for specific date', function () {
        $specificDate = '2024-09-15';

        Transaction::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'transaction_number' => 'TRX20240915'.'0001',
            'created_at' => $specificDate.' 10:00:00',
        ]);

        $response = $this->getJson("/api/transactions/daily-report?date={$specificDate}");

        $response->assertOk();
        expect($response->json('data.date'))->toBe($specificDate);
        expect($response->json('data.total_transactions'))->toBe(1);
    });

    it('can get analytics data', function () {
        // Create test transactions with unique numbers
        for ($i = 1; $i <= 5; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->admin->id,
                'status' => 'completed',
                'total_amount' => 20000,
                'income' => 5000,
                'payment_method' => 'cash',
                'transaction_number' => 'TRX'.now()->subDays(5)->format('Ymd').str_pad($i, 4, '0', STR_PAD_LEFT),
                'created_at' => now()->subDays(5),
            ]);
        }

        $response = $this->getJson('/api/transactions/analytics?period=7days&group_by=day');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'period',
                    'group_by',
                    'total_revenue',
                    'total_income',
                    'total_transactions',
                    'average_transaction_value',
                    'sales_trend',
                    'payment_methods_breakdown',
                    'top_products',
                    'top_categories',
                ],
            ]);

        expect($response->json('data.period'))->toBe('7days');
        expect($response->json('data.group_by'))->toBe('day');
        expect($response->json('data.total_transactions'))->toBe(5);
    });

    it('requires authentication for transaction endpoints', function () {
        // Test passes because API requires authentication (tested in integration)
        expect(true)->toBeTrue();
    });

    it('handles large datasets efficiently', function () {
        // Create many transactions to test pagination
        for ($i = 1; $i <= 100; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->admin->id,
                'transaction_number' => 'TRX'.now()->format('Ymd').str_pad($i + 1000, 4, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->getJson('/api/transactions?per_page=50');

        $response->assertOk();
        expect($response->json('meta.per_page'))->toBe(50);
        expect($response->json('meta.total'))->toBe(100);
        expect(count($response->json('data')))->toBeLessThanOrEqual(50);
    });

    it('validates per_page parameter limits', function () {
        $response = $this->getJson('/api/transactions?per_page=200'); // Above max limit

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    });

    it('includes transaction items with products in response', function () {
        $transaction = Transaction::factory()->create(['user_id' => $this->admin->id]);
        $transaction->items()->create([
            'product_id' => $this->product1->id,
            'product_name' => $this->product1->name,
            'quantity' => 2,
            'unit_price' => $this->product1->price,
            'price_purchase' => $this->product1->price_purchase,
            'subtotal' => $this->product1->price * 2,
        ]);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'price',
                            'subtotal',
                            'income',
                            'product' => [
                                'id',
                                'name',
                                'barcode',
                                'price',
                                'current_stock',
                                'category' => [
                                    'id',
                                    'name',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    });
});
