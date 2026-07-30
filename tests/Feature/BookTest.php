<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーは書籍一覧を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books');

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストは書籍一覧を表示できる()
    {
        $response = $this->get('/books');

        $response->assertStatus(200);
    }

    /** @test */
    public function ログインユーザーは書籍登録画面を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストは書籍登録画面にアクセスするとログイン画面にリダイレクトされる()
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 書籍登録でタイトルが未入力の場合はエラーになる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルを入力してください。',
        ]);
    }

    /** @test */
    public function 書籍登録で著者が未入力の場合はエラーになる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト本',
            'author' => '',
            'isbn' => '9781234567890',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者名を入力してください。',
        ]);
    }

    /** @test */
    public function 書籍登録で_isb_nが未入力の場合はエラーになる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'ISBNを入力してください。',
        ]);
    }

    /** @test */
    public function ログインユーザーは書籍を登録できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
        ]);
    }

    /** @test */
    public function ログインユーザーは自分の書籍を更新できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => $book->isbn,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    /** @test */
    public function ログインユーザーは自分の書籍を削除できる()
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertRedirect('/books');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 他人の書籍は削除できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertForbidden();
    }
}
