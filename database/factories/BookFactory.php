<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title'            => ucwords(implode(' ', $this->faker->words(rand(2, 5)))),
            'description'      => $this->faker->paragraph(),
            'isbn'             => $this->faker->unique()->numerify('978##########'),
            'publication_year' => $this->faker->numberBetween(1950, (int) date('Y')),
            'author_id'        => Author::factory(),
        ];
    }
}