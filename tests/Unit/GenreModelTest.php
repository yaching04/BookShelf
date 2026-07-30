<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Genre;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンルは複数の本を持てる()
    {
        $genre = Genre::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $genre->books()->attach([$book1->id, $book2->id]);

        $this->assertCount(2, $genre->books);
        $this->assertTrue($genre->books->contains($book1));
        $this->assertTrue($genre->books->contains($book2));
    }
}
