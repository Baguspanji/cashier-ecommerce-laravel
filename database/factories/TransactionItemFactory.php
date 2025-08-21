<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $product->price;
        $subtotal = $quantity * $unitPrice;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'price_purchase' => $product->price_purchase,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Set specific product for the transaction item.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            $quantity = $this->faker->numberBetween(1, 5);
            $subtotal = $quantity * $product->price;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'price_purchase' => $product->price_purchase,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        });
    }
}
