<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 5000, 500000);
        $paymentAmount = $this->faker->randomFloat(2, $totalAmount, $totalAmount + 100000);

        return [
            'transaction_number' => Transaction::generateTransactionNumber(),
            'user_id' => User::factory(),
            'total_amount' => $totalAmount,
            'income' => $this->faker->randomFloat(2, $totalAmount * 0.1, $totalAmount * 0.5), // 10-50% profit margin
            'payment_method' => $this->faker->randomElement(['cash', 'debit', 'credit', 'e_wallet']),
            'payment_amount' => $paymentAmount,
            'change_amount' => $paymentAmount - $totalAmount,
            'status' => 'completed',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the transaction is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
