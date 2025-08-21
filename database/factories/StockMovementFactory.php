<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['in', 'out', 'adjustment']);
        $quantity = $this->faker->numberBetween(1, 50);
        $previousStock = $this->faker->numberBetween(0, 100);

        // Calculate new stock based on movement type
        $newStock = match ($type) {
            'in', 'adjustment' => $previousStock + $quantity,
            'out' => $previousStock - $quantity,
            default => $previousStock,
        };

        return [
            'product_id' => Product::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_id' => null,
            'reference_type' => $this->faker->randomElement(['manual', 'initial', 'adjustment']),
            'notes' => $this->faker->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Create an 'in' type stock movement.
     */
    public function stockIn(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(10, 100);
            $previousStock = $attributes['previous_stock'] ?? $this->faker->numberBetween(0, 50);

            return [
                'type' => 'in',
                'quantity' => $quantity,
                'new_stock' => $previousStock + $quantity,
                'reference_type' => 'manual',
            ];
        });
    }

    /**
     * Create an 'out' type stock movement.
     */
    public function stockOut(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(1, 10);
            $previousStock = $attributes['previous_stock'] ?? $this->faker->numberBetween(10, 50);

            return [
                'type' => 'out',
                'quantity' => $quantity,
                'new_stock' => $previousStock - $quantity,
                'reference_type' => 'transaction',
            ];
        });
    }
}
