<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\UserRole;

// Stock Index Tests
describe('StockController Index', function () {
    it('admin can view stock movements index', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Stock/Index')
                ->has('movements')
                ->has('products')
                ->has('filters')
            );
    });

    it('manager can view stock movements index', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($user)
            ->get(route('stock.index'));

        $response->assertOk();
    });

    it('cashier cannot view stock movements index', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);

        $response = $this->actingAs($user)
            ->get(route('stock.index'));

        $response->assertForbidden();
    });

    it('guest cannot view stock movements index', function () {
        $response = $this->get(route('stock.index'));

        $response->assertRedirect(route('login'));
    });

    it('can filter stock movements by product', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        StockMovement::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
        ]);
        StockMovement::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.index', ['product_id' => $product1->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('movements.data', 1)
                ->where('movements.data.0.product_id', $product1->id)
            );
    });

    it('can filter stock movements by type', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'in',
        ]);
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'out',
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.index', ['type' => 'in']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('movements.data', 1)
                ->where('movements.data.0.type', 'in')
            );
    });

    it('can filter stock movements by date range', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'created_at' => $today,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.index', [
                'date_from' => $today,
                'date_to' => $today,
            ]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('movements.data', 1)
            );
    });
});

// Stock Create Tests
describe('StockController Create', function () {
    it('admin can view stock adjustment create form', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($user)
            ->get(route('stock.create'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Stock/Create')
                ->has('products')
            );
    });

    it('manager can view stock adjustment create form', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($user)
            ->get(route('stock.create'));

        $response->assertOk();
    });

    it('cashier cannot view stock adjustment create form', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);

        $response = $this->actingAs($user)
            ->get(route('stock.create'));

        $response->assertForbidden();
    });
});

// Stock Store Tests
describe('StockController Store', function () {
    it('admin can create stock adjustment - in type', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Stock replenishment',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertRedirect(route('stock.index'))
            ->assertSessionHas('success', 'Stok penambahan berhasil dicatat.');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Stock replenishment',
            'user_id' => $user->id,
        ]);

        // Check product stock was updated
        $product->refresh();
        $this->assertEquals(15, $product->current_stock);
    });

    it('admin can create stock adjustment - out type', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'notes' => 'Damaged items',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertRedirect(route('stock.index'))
            ->assertSessionHas('success', 'Stok pengurangan berhasil dicatat.');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'notes' => 'Damaged items',
            'user_id' => $user->id,
        ]);

        // Check product stock was updated
        $product->refresh();
        $this->assertEquals(7, $product->current_stock);
    });

    it('admin can create stock adjustment - adjustment type', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 2,
            'notes' => 'Stock audit adjustment',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertRedirect(route('stock.index'))
            ->assertSessionHas('success', 'Stok penyesuaian berhasil dicatat.');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 2,
            'notes' => 'Stock audit adjustment',
            'user_id' => $user->id,
        ]);
    });

    it('allows stock movements that would result in negative stock', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 5,
        ]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 10, // More than available stock
            'notes' => 'Allowing negative stock',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertRedirect(route('stock.index'))
            ->assertSessionHas('success', 'Stok pengurangan berhasil dicatat.');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 10,
            'type' => 'out',
        ]);

        // Check product stock went negative
        $product->refresh();
        $this->assertEquals(-5, $product->current_stock); // 5 - 10 = -5
    });

    it('validates required fields when creating stock movement', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($user)
            ->post(route('stock.store'), []);

        $response->assertSessionHasErrors(['product_id', 'type', 'quantity']);
    });

    it('validates product exists when creating stock movement', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $stockData = [
            'product_id' => 999999,
            'type' => 'in',
            'quantity' => 5,
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertSessionHasErrors(['product_id']);
    });

    it('validates type is valid when creating stock movement', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'invalid_type',
            'quantity' => 5,
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertSessionHasErrors(['type']);
    });

    it('validates quantity is positive when creating stock movement', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 0,
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertSessionHasErrors(['quantity']);
    });

    it('manager can create stock adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertRedirect(route('stock.index'));
    });

    it('cashier cannot create stock adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $stockData = [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.store'), $stockData);

        $response->assertForbidden();
    });
});

// Stock Overview Tests
describe('StockController Overview', function () {
    it('admin can view stock overview', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Stock/Overview')
                ->has('products')
                ->has('categories')
                ->has('summary')
                ->has('filters')
            );
    });

    it('can filter products by category in overview', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview', ['category_id' => $category1->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.category_id', $category1->id)
            );
    });

    it('can filter products by low stock status', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
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

        $response = $this->actingAs($user)
            ->get(route('stock.overview', ['stock_status' => 'low']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
            );
    });

    it('can filter products by out of stock status', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();

        // Out of stock product
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 0,
        ]);

        // In stock product
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview', ['stock_status' => 'out']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.current_stock', 0)
            );
    });

    it('shows correct summary statistics', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();

        // Normal product
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
            'price' => 1000,
        ]);

        // Low stock product
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 2,
            'minimum_stock' => 5,
            'price' => 2000,
        ]);

        // Out of stock product
        Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 0,
            'minimum_stock' => 5,
            'price' => 1500,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('summary.total_products', 3)
                ->where('summary.low_stock_count', 2) // 2 products have stock <= minimum
                ->where('summary.out_of_stock_count', 1)
                ->where('summary.total_stock_value', 14000) // (10*1000) + (2*2000) + (0*1500)
            );
    });

    it('manager can view stock overview', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview'));

        $response->assertOk();
    });

    it('cashier cannot view stock overview', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);

        $response = $this->actingAs($user)
            ->get(route('stock.overview'));

        $response->assertForbidden();
    });
});

// Product Movements Tests
describe('StockController Product Movements', function () {
    it('admin can view product movements', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('stock.product-movements', $product));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Stock/ProductMovements')
                ->has('product')
                ->has('movements')
                ->where('product.id', $product->id)
            );
    });

    it('manager can view product movements', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)
            ->get(route('stock.product-movements', $product));

        $response->assertOk();
    });

    it('cashier cannot view product movements', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)
            ->get(route('stock.product-movements', $product));

        $response->assertForbidden();
    });
});

// Bulk Adjustment Tests
describe('StockController Bulk Adjustment', function () {
    it('admin can perform bulk stock adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 5,
        ]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => $product1->id,
                    'new_stock' => 15, // increase by 5
                ],
                [
                    'product_id' => $product2->id,
                    'new_stock' => 3, // decrease by 2
                ],
            ],
            'notes' => 'Monthly stock audit',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertRedirect(route('stock.overview'))
            ->assertSessionHas('success', 'Penyesuaian stok massal berhasil dilakukan.');

        // Check stock movements were created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product1->id,
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Monthly stock audit',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product2->id,
            'type' => 'out',
            'quantity' => 2,
            'notes' => 'Monthly stock audit',
        ]);

        // Check product stocks were updated
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(15, $product1->current_stock);
        $this->assertEquals(3, $product2->current_stock);
    });

    it('ignores products with no stock change in bulk adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10,
        ]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => $product->id,
                    'new_stock' => 10, // Same as current stock
                ],
            ],
            'notes' => 'No change needed',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertRedirect(route('stock.overview'));

        // No stock movement should be created
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'notes' => 'No change needed',
        ]);
    });

    it('validates bulk adjustment data', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), []);

        $response->assertSessionHasErrors(['adjustments']);
    });

    it('validates product exists in bulk adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => 999999,
                    'new_stock' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertSessionHasErrors(['adjustments.0.product_id']);
    });

    it('allows negative stock in bulk adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 10
        ]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => $product->id,
                    'new_stock' => -5,
                ],
            ],
            'notes' => 'Allowing negative stock',
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertRedirect(route('stock.overview'))
            ->assertSessionHas('success', 'Penyesuaian stok massal berhasil dilakukan.');

        // Check stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 15, // 10 - (-5) = 15
            'notes' => 'Allowing negative stock',
        ]);

        // Check product stock went negative
        $product->refresh();
        $this->assertEquals(-5, $product->current_stock);
    });

    it('manager can perform bulk stock adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => $product->id,
                    'new_stock' => 15,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertRedirect(route('stock.overview'));
    });

    it('cashier cannot perform bulk stock adjustment', function () {
        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $adjustmentData = [
            'adjustments' => [
                [
                    'product_id' => $product->id,
                    'new_stock' => 15,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('stock.bulk-adjustment'), $adjustmentData);

        $response->assertForbidden();
    });
});

// Authorization Tests
describe('StockController Authorization', function () {
    it('all methods require authentication', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Test all routes without authentication
        $routes = [
            ['GET', route('stock.index')],
            ['GET', route('stock.create')],
            ['POST', route('stock.store')],
            ['GET', route('stock.overview')],
            ['GET', route('stock.product-movements', $product)],
            ['POST', route('stock.bulk-adjustment')],
        ];

        foreach ($routes as [$method, $route]) {
            $response = $this->call($method, $route);
            $response->assertRedirect(route('login'));
        }
    });
});
