<?php

namespace Database\Factories;

use App\Enums\CopyStatus;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookCopy>
 */
class BookCopyFactory extends Factory
{
    protected $model = BookCopy::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'status' => CopyStatus::AVAILABLE,
        ];
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => CopyStatus::AVAILABLE]);
    }

    public function borrowed(): static
    {
        return $this->state(fn () => ['status' => CopyStatus::BORROWED]);
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => CopyStatus::RESERVED]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => CopyStatus::LOST]);
    }
}