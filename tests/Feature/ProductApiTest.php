<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($this->admin);
});

test('can list products via API', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);

    $response = $this->getJson('/api/products');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
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
                    'category',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta'
        ]);
});

test('can search products via API', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['name' => 'Laptop Gaming', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Mouse Gaming', 'category_id' => $category->id]);

    $response = $this->getJson('/api/products?search=Laptop');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Laptop Gaming');
});

test('can filter products by category via API', function () {
    $electronicsCategory = Category::factory()->create(['name' => 'Electronics']);
    $foodCategory = Category::factory()->create(['name' => 'Food']);

    Product::factory()->create(['category_id' => $electronicsCategory->id]);
    Product::factory()->create(['category_id' => $foodCategory->id]);

    $response = $this->getJson("/api/products?category_id={$electronicsCategory->id}");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.category_id', $electronicsCategory->id);
});

test('can filter products by status via API', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['is_active' => true, 'category_id' => $category->id]);
    Product::factory()->create(['is_active' => false, 'category_id' => $category->id]);

    $response = $this->getJson('/api/products?status=active');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_active', true);
});

test('can create product via API', function () {
    $category = Category::factory()->create();
    $productData = [
        'name' => 'New Product',
        'barcode' => '1234567890123',
        'description' => 'Product description',
        'price' => '100000',
        'price_purchase' => '80000',
        'category_id' => $category->id,
        'current_stock' => 10,
        'minimum_stock' => 5,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/products', $productData);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'barcode',
                'description',
                'price',
                'category',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.name', 'New Product');

    $this->assertDatabaseHas('products', array_merge($productData, ['price' => 100000]));
});

test('validates required fields when creating product via API', function () {
    $response = $this->postJson('/api/products', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'category_id', 'current_stock', 'minimum_stock']);
});

test('validates unique barcode when creating product via API', function () {
    $category = Category::factory()->create();
    $existingProduct = Product::factory()->create(['barcode' => '1234567890123', 'category_id' => $category->id]);

    $productData = [
        'name' => 'New Product',
        'barcode' => '1234567890123',
        'price' => '100000',
        'category_id' => $category->id,
        'current_stock' => 10,
        'minimum_stock' => 5,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/products', $productData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['barcode']);
});

test('can show specific product via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'barcode',
                'description',
                'price',
                'category',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.id', $product->id);
});

test('can update product via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $updateData = [
        'name' => 'Updated Product',
        'description' => 'Updated description',
        'price' => '120000',
        'price_purchase' => '90000',
        'category_id' => $category->id,
        'current_stock' => 15,
        'minimum_stock' => 8,
        'is_active' => true,
    ];

    $response = $this->putJson("/api/products/{$product->id}", $updateData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.name', 'Updated Product');

    $this->assertDatabaseHas('products', array_merge(['id' => $product->id], $updateData, ['price' => 120000]));
});

test('can delete product via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertSuccessful()
        ->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('cannot delete product with transaction history via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $transaction = Transaction::factory()->create();
    TransactionItem::factory()->create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id
    ]);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'error'])
        ->assertJsonPath('error', 'has_transactions');

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('can search product by barcode via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'barcode' => '1234567890123',
        'category_id' => $category->id
    ]);

    $response = $this->getJson('/api/products/search-barcode/1234567890123');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'barcode',
                'category',
            ]
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $product->id);
});

test('returns 404 when barcode not found via API', function () {
    $response = $this->getJson('/api/products/search-barcode/nonexistent');

    $response->assertNotFound()
        ->assertJsonPath('success', false);
});

test('can toggle product status via API', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['is_active' => true, 'category_id' => $category->id]);

    $response = $this->patchJson("/api/products/{$product->id}/toggle-status");

    $response->assertSuccessful()
        ->assertJsonStructure(['message', 'data'])
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => false
    ]);
});

test('unauthorized user cannot access product API', function () {
    // Create a fresh test instance without authentication
    $this->refreshApplication();

    $response = $this->getJson('/api/products');

    $response->assertUnauthorized();
});
