<?php

namespace Database\Factories;

use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyncLog>
 */
class SyncLogFactory extends Factory
{
    protected $model = SyncLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'operation' => $this->faker->randomElement(['create', 'update', 'delete']),
            'table_name' => $this->faker->randomElement(['transactions', 'transaction_items', 'stock_movements']),
            'record_id' => $this->faker->numberBetween(1, 1000),
            'offline_id' => $this->faker->uuid(),
            'data' => [
                'example_field' => $this->faker->word(),
                'timestamp' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            ],
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'error_message' => null,
            'synced_at' => null,
        ];
    }

    /**
     * Indicate that the sync log is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'synced_at' => null,
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the sync log is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'synced_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the sync log failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'synced_at' => null,
            'error_message' => $this->faker->sentence(),
        ]);
    }
}
