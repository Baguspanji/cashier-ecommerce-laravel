<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\TransactionItem;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ProductController Index', function () {
    it('admin can view products index', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->get(route('products.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 3)
                ->has('categories')
                ->has('filters')
            );
    });

    it('manager can view products index', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->actingAs($manager)
            ->get(route('products.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 2)
                ->has('categories')
                ->has('filters')
            );
    });

    it('cashier cannot view products index', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($cashier)
            ->get(route('products.index'));

        $response->assertForbidden();
    });

    it('guest cannot view products index', function () {
        $response = $this->get(route('products.index'));

        $response->assertRedirect('/login');
    });

    it('can filter products by search term', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();

        Product::factory()->create([
            'name' => 'Laptop Gaming',
            'category_id' => $category->id,
        ]);
        Product::factory()->create([
            'name' => 'Mouse Wireless',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('products.index', ['search' => 'Laptop']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 1)
                ->where('filters.search', 'Laptop')
            );
    });

    it('can filter products by category', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($admin)
            ->get(route('products.index', ['category_id' => $category1->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 2)
                ->where('filters.category_id', (string) $category1->id)
            );
    });

    it('can filter products by status', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();

        Product::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);
        Product::factory()->create([
            'is_active' => false,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('products.index', ['status' => 'active']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 1)
                ->where('filters.status', 'active')
            );
    });
});

describe('ProductController Create', function () {
    it('admin can view create form', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        Category::factory()->count(2)->create();

        $response = $this->actingAs($admin)
            ->get(route('products.create'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Create')
                ->has('categories', 2)
            );
    });

    it('manager can view create form', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        Category::factory()->create();

        $response = $this->actingAs($manager)
            ->get(route('products.create'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Create')
                ->has('categories')
            );
    });

    it('cashier cannot view create form', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($cashier)
            ->get(route('products.create'));

        $response->assertForbidden();
    });
});

describe('ProductController Store', function () {
    it('admin can create a new product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product description',
            'price' => '99.99',
            'category_id' => $category->id,
            'current_stock' => 100,
            'minimum_stock' => 10,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'A test product description',
            'price' => '99.99',
            'category_id' => $category->id,
            'current_stock' => 100,
            'minimum_stock' => 10,
            'is_active' => true,
        ]);
    });

    it('manager can create a new product', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Manager Product',
            'description' => 'Product created by manager',
            'price' => '49.99',
            'category_id' => $category->id,
            'current_stock' => 50,
            'minimum_stock' => 5,
            'is_active' => true,
        ];

        $response = $this->actingAs($manager)
            ->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('products', [
            'name' => 'Manager Product',
            'price' => '49.99',
        ]);
    });

    it('cashier cannot create a new product', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Cashier Product',
            'price' => '29.99',
            'category_id' => $category->id,
            'current_stock' => 25,
            'minimum_stock' => 2,
            'is_active' => true,
        ];

        $response = $this->actingAs($cashier)
            ->post(route('products.store'), $productData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('products', [
            'name' => 'Cashier Product',
        ]);
    });

    it('validates required fields when creating product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $response = $this->actingAs($admin)
            ->post(route('products.store'), []);

        $response->assertSessionHasErrors(['name', 'price', 'category_id', 'current_stock', 'minimum_stock']);
    });

    it('validates price is numeric when creating product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'price' => 'invalid-price',
            'category_id' => $category->id,
            'current_stock' => 100,
            'minimum_stock' => 10,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $productData);

        $response->assertSessionHasErrors(['price']);
    });

    it('validates category exists when creating product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $productData = [
            'name' => 'Test Product',
            'price' => '99.99',
            'category_id' => 999, // Non-existent category
            'current_stock' => 100,
            'minimum_stock' => 10,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $productData);

        $response->assertSessionHasErrors(['category_id']);
    });

    it('validates stock values are non-negative when creating product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'price' => '99.99',
            'category_id' => $category->id,
            'current_stock' => -5,
            'minimum_stock' => -2,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $productData);

        $response->assertSessionHasErrors(['current_stock', 'minimum_stock']);
    });
});

describe('ProductController Show', function () {
    it('admin can view product details', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->get(route('products.show', $product));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Show')
                ->has('product')
                ->where('product.id', $product->id)
            );
    });

    it('manager can view product details', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($manager)
            ->get(route('products.show', $product));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Show')
                ->where('product.id', $product->id)
            );
    });

    it('cashier cannot view product details', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($cashier)
            ->get(route('products.show', $product));

        $response->assertForbidden();
    });
});

describe('ProductController Edit', function () {
    it('admin can view edit form', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->get(route('products.edit', $product));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Edit')
                ->has('product')
                ->has('categories')
                ->where('product.id', $product->id)
            );
    });

    it('manager can view edit form', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($manager)
            ->get(route('products.edit', $product));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Edit')
                ->where('product.id', $product->id)
            );
    });

    it('cashier cannot view edit form', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($cashier)
            ->get(route('products.edit', $product));

        $response->assertForbidden();
    });
});

describe('ProductController Update', function () {
    it('admin can update product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'price' => '50.00',
            'category_id' => $category->id,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => '75.99',
            'category_id' => $category->id,
            'current_stock' => 200,
            'minimum_stock' => 20,
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)
            ->patch(route('products.update', $product), $updateData);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil diperbarui.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => '75.99',
            'current_stock' => 200,
            'minimum_stock' => 20,
            'is_active' => false,
        ]);
    });

    it('manager can update product', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $updateData = [
            'name' => 'Manager Updated Product',
            'price' => '89.99',
            'category_id' => $category->id,
            'current_stock' => $product->current_stock,
            'minimum_stock' => $product->minimum_stock,
            'is_active' => true,
        ];

        $response = $this->actingAs($manager)
            ->patch(route('products.update', $product), $updateData);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil diperbarui.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Manager Updated Product',
            'price' => '89.99',
        ]);
    });

    it('cashier cannot update product', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $updateData = [
            'name' => 'Cashier Updated Product',
            'price' => '39.99',
            'category_id' => $category->id,
            'current_stock' => $product->current_stock,
            'minimum_stock' => $product->minimum_stock,
            'is_active' => true,
        ];

        $response = $this->actingAs($cashier)
            ->patch(route('products.update', $product), $updateData);

        $response->assertForbidden();
    });

    it('validates required fields when updating product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->patch(route('products.update', $product), []);

        $response->assertSessionHasErrors(['name', 'price', 'category_id', 'current_stock', 'minimum_stock']);
    });
});

describe('ProductController Destroy', function () {
    it('admin can delete product without transaction history', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil dihapus.');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    });

    it('manager can delete product without transaction history', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($manager)
            ->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Produk berhasil dihapus.');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    });

    it('cashier cannot delete product', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($cashier)
            ->delete(route('products.destroy', $product));

        $response->assertForbidden();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    });

    it('cannot delete product with transaction history', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Create a transaction item for this product
        TransactionItem::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($admin)
            ->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    });
});

describe('ProductController ToggleStatus', function () {
    it('admin can toggle product status', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('products.toggle-status', $product));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Produk berhasil dinonaktifkan.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    });

    it('manager can toggle product status', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'is_active' => false,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($manager)
            ->patch(route('products.toggle-status', $product));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Produk berhasil diaktifkan.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    });

    it('cashier cannot toggle product status', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($cashier)
            ->patch(route('products.toggle-status', $product));

        $response->assertForbidden();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    });
});

describe('ProductController Authorization', function () {
    it('all methods require authentication', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Test all routes require authentication
        $this->get(route('products.index'))->assertRedirect('/login');
        $this->get(route('products.create'))->assertRedirect('/login');
        $this->post(route('products.store'), [])->assertRedirect('/login');
        $this->get(route('products.show', $product))->assertRedirect('/login');
        $this->get(route('products.edit', $product))->assertRedirect('/login');
        $this->patch(route('products.update', $product), [])->assertRedirect('/login');
        $this->delete(route('products.destroy', $product))->assertRedirect('/login');
        $this->patch(route('products.toggle-status', $product))->assertRedirect('/login');
    });
});
