<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create users
        $admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
            'role'  => Role::ADMIN,
        ]);

        $librarian = User::factory()->create([
            'name'  => 'Librarian User',
            'email' => 'librarian@example.com',
            'role'  => Role::LIBRARIAN,
        ]);

        $member = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'role'  => Role::MEMBER,
        ]);

        // Create categories
        $categories = Category::factory(5)->create();

        // Create authors
        $authors = Author::factory(8)->create();

        // Create books with relationships
        Book::factory(20)->create()->each(function (Book $book) use ($categories) {
            $book->categories()->sync(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}