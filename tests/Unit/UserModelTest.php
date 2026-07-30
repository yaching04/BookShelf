<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーは複数の本を持てる()
    {
        $user = User::factory()->create();

        Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(3, $user->books);
    }

    /** @test */
    public function ユーザーは複数のレビューを持てる()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
        ]);

        $this->assertCount(2, $user->reviews);
    }

    /** @test */
    public function ユーザーは本をお気に入りできる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue($user->favoriteBooks->contains($book));
        $this->assertCount(1, $user->favoriteBooks);
    }

    /** @test */
    public function ユーザーはレビューにいいねできる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $this->assertTrue($user->likedReviews->contains($review));
        $this->assertCount(1, $user->likedReviews);
    }
}
