<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->numerify('978##########'),
            'published_date' => $this->faker->optional()->date(),
            'description' => $this->faker->optional()->paragraph(),
            'image_url' => $this->faker->optional()->imageUrl(),
        ];
    }
}
