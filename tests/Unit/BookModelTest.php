<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 本はユーザーに属している()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /** @test */
    public function 本は複数のジャンルを持てる()
    {
        $book = Book::factory()->create();
        $genre1 = Genre::factory()->create(['name' => '小説']);
        $genre2 = Genre::factory()->create(['name' => 'ビジネス']);

        $book->genres()->attach([$genre1->id, $genre2->id]);

        $this->assertCount(2, $book->genres);
        $this->assertTrue($book->genres->contains($genre1));
        $this->assertTrue($book->genres->contains($genre2));
    }

    /** @test */
    public function 本は複数のレビューを持てる()
    {
        $book = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user1->id,
        ]);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user2->id,
        ]);

        $this->assertCount(2, $book->reviews);
    }
}
