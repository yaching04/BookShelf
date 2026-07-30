<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーはお気に入り一覧を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストはお気に入り一覧にアクセスするとログイン画面にリダイレクトされる()
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログインユーザーはお気に入りに追加できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function ログインユーザーはお気に入りを解除できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 先にお気に入りに追加
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function ゲストはお気に入り操作をするとログイン画面にリダイレクトされる()
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/favorite");

        $response->assertRedirect('/login');
    }
}
