<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($this->admin);
});

test('can list categories via API', function () {
    Category::factory()->count(3)->create();

    $response = $this->getJson('/api/categories');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'products_count',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta'
        ]);
});

test('can search categories via API', function () {
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Food']);

    $response = $this->getJson('/api/categories?search=Electronic');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Electronics');
});

test('can create category via API', function () {
    $categoryData = [
        'name' => 'New Category',
        'description' => 'Category description'
    ];

    $response = $this->postJson('/api/categories', $categoryData);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.name', 'New Category');

    $this->assertDatabaseHas('categories', $categoryData);
});

test('validates required fields when creating category via API', function () {
    $response = $this->postJson('/api/categories', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('can show specific category via API', function () {
    $category = Category::factory()->create();

    $response = $this->getJson("/api/categories/{$category->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'products_count',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.id', $category->id);
});

test('can update category via API', function () {
    $category = Category::factory()->create();
    $updateData = [
        'name' => 'Updated Category',
        'description' => 'Updated description'
    ];

    $response = $this->putJson("/api/categories/{$category->id}", $updateData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'created_at',
                'updated_at',
            ]
        ])
        ->assertJsonPath('data.name', 'Updated Category');

    $this->assertDatabaseHas('categories', array_merge(['id' => $category->id], $updateData));
});

test('can delete category via API', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertSuccessful()
        ->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('cannot delete category with products via API', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'error'])
        ->assertJsonPath('error', 'has_products');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('unauthorized user cannot access category API', function () {
    // Create a fresh test instance without authentication
    $this->refreshApplication();

    $response = $this->getJson('/api/categories');

    $response->assertUnauthorized();
});
