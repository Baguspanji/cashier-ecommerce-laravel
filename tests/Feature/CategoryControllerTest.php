<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view categories index', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    Category::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('categories.index'));

    $response->assertOk();
});

test('admin can create a category', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);

    $categoryData = [
        'name' => 'Electronics',
        'description' => 'Electronic devices and accessories',
    ];

    $response = $this->actingAs($user)
        ->post(route('categories.store'), $categoryData);

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('categories', $categoryData);
});

test('admin can update a category', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $category = Category::factory()->create();

    $updatedData = [
        'name' => 'Updated Category',
        'description' => 'Updated description',
    ];

    $response = $this->actingAs($user)
        ->put(route('categories.update', $category), $updatedData);

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        ...$updatedData,
    ]);
});

test('admin cannot delete category with products', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('admin can delete category without products', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('category name is required', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);

    $response = $this->actingAs($user)
        ->post(route('categories.store'), [
            'description' => 'Category without name',
        ]);

    $response->assertSessionHasErrors('name');
});

test('guest cannot access categories', function () {
    $response = $this->get(route('categories.index'));

    $response->assertRedirect(route('login'));
});

test('manager can manage categories', function () {
    $user = User::factory()->create(['role' => UserRole::Manager->value]);

    $response = $this->actingAs($user)
        ->get(route('categories.index'));

    $response->assertOk();
});

test('cashier cannot manage categories', function () {
    $user = User::factory()->create(['role' => UserRole::Cashier->value]);

    $response = $this->actingAs($user)
        ->get(route('categories.index'));

    $response->assertForbidden();
});

test('cashier cannot create category', function () {
    $user = User::factory()->create(['role' => UserRole::Cashier->value]);

    $categoryData = [
        'name' => 'Test Category',
        'description' => 'Test description',
    ];

    $response = $this->actingAs($user)
        ->post(route('categories.store'), $categoryData);

    $response->assertForbidden();
});
