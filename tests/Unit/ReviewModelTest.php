<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューはユーザーに属している()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /** @test */
    public function レビューは本に属している()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /** @test */
    public function レビューは複数のいいねを持てる()
    {
        $review = Review::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // likedByUsers を使う（多対多）
        $review->likedByUsers()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $review->likedByUsers);
    }
}
