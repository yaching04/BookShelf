<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーはレビューを投稿できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating'  => 5,
            'comment' => 'とても良い本でした！',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating'  => 5,
            'comment' => 'とても良い本でした！',
        ]);
    }

    /** @test */
    public function レビュー投稿で評価が未入力の場合はエラーになる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating'  => '',
            'comment' => 'コメントだけ',
        ]);

        $response->assertSessionHasErrors([
            'rating' => '評価は必須です。',
        ]);
    }

    /** @test */
    public function ログインユーザーは自分のレビューを更新できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating'  => 3,
            'comment' => '普通でした',
        ]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating'  => 5,
            'comment' => 'やっぱり良かった！',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id'      => $review->id,
            'rating'  => 5,
            'comment' => 'やっぱり良かった！',
        ]);
    }

    /** @test */
    public function 他人のレビューは更新できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating'  => 1,
            'comment' => '改ざん',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function ログインユーザーは自分のレビューを削除できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function 他人のレビューは削除できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertForbidden();
    }
}
