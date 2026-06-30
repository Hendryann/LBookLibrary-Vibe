<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'queue_position' => 1,
            'reserved_at' => now(),
            'status' => ReservationStatus::PENDING,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::PENDING]);
    }

    public function fulfilled(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::FULFILLED]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::CANCELLED]);
    }
}
