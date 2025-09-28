<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($this->admin);
});

test('can list stock movements via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    StockMovement::factory()->count(3)->create(['product_id' => $product->id]);

    $response = $this->getJson('/api/stock');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'product_id',
                    'type',
                    'quantity',
                    'notes',
                    'user_id',
                    'product',
                    'user',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta'
        ]);
});

test('can filter stock movements by product via API', function () {
    $category = Category::factory()->create();
    $product1 = Product::factory()->create(['category_id' => $category->id]);
    $product2 = Product::factory()->create(['category_id' => $category->id]);

    StockMovement::factory()->create(['product_id' => $product1->id]);
    StockMovement::factory()->create(['product_id' => $product2->id]);

    $response = $this->getJson("/api/stock?product_id={$product1->id}");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.product_id', $product1->id);
});

test('can filter stock movements by type via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    StockMovement::factory()->create(['product_id' => $product->id, 'type' => 'in']);
    StockMovement::factory()->create(['product_id' => $product->id, 'type' => 'out']);

    $response = $this->getJson('/api/stock?type=in');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'in');
});

test('can create stock movement via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'current_stock' => 10]);

    $stockData = [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 5,
        'notes' => 'Test stock addition',
    ];

    $response = $this->postJson('/api/stock', $stockData);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'product_id',
                'type',
                'quantity',
                'notes',
                'product',
                'user',
            ]
        ])
        ->assertJsonPath('data.type', 'in')
        ->assertJsonPath('data.quantity', 5);

    $this->assertDatabaseHas('stock_movements', array_merge($stockData, ['user_id' => $this->admin->id]));

    // Check if product stock was updated
    expect($product->fresh()->current_stock)->toBe(15);
});

test('validates required fields when creating stock movement via API', function () {
    $response = $this->postJson('/api/stock', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id', 'type', 'quantity']);
});

test('validates stock movement type via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->postJson('/api/stock', [
        'product_id' => $product->id,
        'type' => 'invalid_type',
        'quantity' => 5,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('can get stock overview via API', function () {
    $category = Category::factory()->create();
    Product::factory()->count(5)->create(['category_id' => $category->id]);

    $response = $this->getJson('/api/stock/overview');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'products' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'current_stock',
                        'minimum_stock',
                        'category',
                    ]
                ],
                'links',
                'meta',
            ],
            'summary' => [
                'total_products',
                'low_stock_count',
                'out_of_stock_count',
                'total_stock_value',
            ],
        ])
        ->assertJsonCount(5, 'products.data');
});

test('can filter stock overview by category via API', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->create(['category_id' => $category1->id]);
    Product::factory()->create(['category_id' => $category2->id]);

    $response = $this->getJson("/api/stock/overview?category_id={$category1->id}");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'products.data')
        ->assertJsonPath('products.data.0.category_id', $category1->id);
});

test('can filter stock overview by stock status via API', function () {
    $category = Category::factory()->create();

    // Low stock product
    Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 2,
        'minimum_stock' => 5,
    ]);

    // Normal stock product
    Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 10,
        'minimum_stock' => 5,
    ]);

    $response = $this->getJson('/api/stock/overview?stock_status=low');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'products.data');
});

test('can get product movements via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    StockMovement::factory()->count(3)->create(['product_id' => $product->id]);

    $response = $this->getJson("/api/stock/products/{$product->id}/movements");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'product' => [
                'id',
                'name',
                'current_stock',
                'category',
            ],
            'movements' => [
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'quantity',
                        'notes',
                        'created_at',
                    ]
                ],
                'links',
                'meta',
            ],
        ])
        ->assertJsonPath('product.id', $product->id)
        ->assertJsonCount(3, 'movements.data');
});

test('can perform bulk stock adjustment via API', function () {
    $category = Category::factory()->create();
    $product1 = Product::factory()->create(['category_id' => $category->id, 'current_stock' => 10]);
    $product2 = Product::factory()->create(['category_id' => $category->id, 'current_stock' => 5]);

    $adjustmentData = [
        'adjustments' => [
            [
                'product_id' => $product1->id,
                'new_stock' => 15, // increase by 5
            ],
            [
                'product_id' => $product2->id,
                'new_stock' => 3, // decrease by 2
            ]
        ],
        'notes' => 'Bulk adjustment test',
    ];

    $response = $this->postJson('/api/stock/bulk-adjustment', $adjustmentData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'processed_adjustments',
            'total_processed',
        ])
        ->assertJsonPath('total_processed', 2);

    // Check if stocks were updated
    expect($product1->fresh()->current_stock)->toBe(15);
    expect($product2->fresh()->current_stock)->toBe(3);

    // Check if stock movements were created
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product1->id,
        'type' => 'in',
        'quantity' => 5,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product2->id,
        'type' => 'out',
        'quantity' => 2,
    ]);
});

test('can get stock alerts via API', function () {
    $category = Category::factory()->create();

    // Low stock product
    $lowStockProduct = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 2,
        'minimum_stock' => 5,
    ]);

    // Out of stock product
    $outOfStockProduct = Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 0,
        'minimum_stock' => 5,
    ]);

    // Normal stock product
    Product::factory()->create([
        'category_id' => $category->id,
        'current_stock' => 10,
        'minimum_stock' => 5,
    ]);

    $response = $this->getJson('/api/stock/alerts');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'low_stock' => [
                '*' => [
                    'id',
                    'name',
                    'current_stock',
                    'minimum_stock',
                ]
            ],
            'out_of_stock' => [
                '*' => [
                    'id',
                    'name',
                    'current_stock',
                    'minimum_stock',
                ]
            ],
            'counts' => [
                'low_stock',
                'out_of_stock',
            ]
        ])
        ->assertJsonCount(1, 'low_stock')
        ->assertJsonCount(1, 'out_of_stock')
        ->assertJsonPath('counts.low_stock', 1)
        ->assertJsonPath('counts.out_of_stock', 1);
});

test('validates bulk adjustment data via API', function () {
    $response = $this->postJson('/api/stock/bulk-adjustment', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['adjustments']);
});


test('unauthorized user cannot access stock API', function () {
    $this->refreshApplication();

    $response = $this->getJson('/api/stock');

    $response->assertUnauthorized();
});
