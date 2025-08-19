<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' ' . $this->faker->word() . ' ' . $this->faker->word(),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 1000, 100000), // Price between 1000-100000
            'image_path' => null,
            'category_id' => Category::factory(),
            'current_stock' => $this->faker->numberBetween(0, 100),
            'minimum_stock' => $this->faker->numberBetween(5, 20),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => 0,
        ]);
    }

    /**
     * Indicate that the product is low on stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => $this->faker->numberBetween(1, 4),
            'minimum_stock' => 5,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product has no barcode (will be auto-generated).
     */
    public function withoutBarcode(): static
    {
        return $this->state(fn (array $attributes) => [
            'barcode' => null,
        ]);
    }

    /**
     * Set a specific barcode for the product.
     */
    public function withBarcode(string $barcode): static
    {
        return $this->state(fn (array $attributes) => [
            'barcode' => $barcode,
        ]);
    }
}
