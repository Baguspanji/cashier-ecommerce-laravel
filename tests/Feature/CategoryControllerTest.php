<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticated user can view categories index', function () {
    $user = User::factory()->create();
    Category::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('categories.index'));

    $response->assertOk();
});

test('user can create a category', function () {
    $user = User::factory()->create();

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

test('user can update a category', function () {
    $user = User::factory()->create();
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

test('user cannot delete category with products', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('user can delete category without products', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('category name is required', function () {
    $user = User::factory()->create();

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
