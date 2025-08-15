<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category can be created with valid data', function () {
    $category = Category::create([
        'name' => 'Test Category',
        'description' => 'Test description',
    ]);

    expect($category)
        ->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->description)->toBe('Test description');
});

test('category has products relationship', function () {
    $category = Category::factory()->create();
    $products = Product::factory(3)->create(['category_id' => $category->id]);

    expect($category->products)->toHaveCount(3)
        ->and($category->products->first())->toBeInstanceOf(Product::class);
});

test('category can be created without description', function () {
    $category = Category::create([
        'name' => 'Test Category',
    ]);

    expect($category->name)->toBe('Test Category')
        ->and($category->description)->toBeNull();
});

test('category factory creates valid category', function () {
    $category = Category::factory()->create();

    expect($category)
        ->toBeInstanceOf(Category::class)
        ->and($category->name)->toBeString()
        ->and($category->created_at)->not->toBeNull();
});

test('category name is required', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Category::create([
        'description' => 'Test description',
    ]);
});

test('category can have many products', function () {
    $category = Category::factory()->create();

    expect($category->products())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
