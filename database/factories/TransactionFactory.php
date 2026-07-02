<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
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
        return [
            'user_id' => \App\Models\User::factory(),
            'copy_id' => \App\Models\BookCopy::factory(),
            'borrow_date' => now(),
            'due_date' => now()->addDays(14),
            'return_date' => null,
            'status' => \App\Enums\TransactionStatus::ACTIVE,
            'fine_amount' => 0,
        ];
    }
}
