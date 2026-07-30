<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーはジャンル一覧を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストはジャンル一覧にアクセスするとログイン画面にリダイレクトされる()
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログインユーザーはジャンルを登録できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    /** @test */
    public function ジャンル登録で名前が未入力の場合はエラーになる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ログインユーザーはジャンルを更新できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '更新前',
        ]);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", [
            'name' => '更新後',
        ]);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id'   => $genre->id,
            'name' => '更新後',
        ]);
    }

    /** @test */
    public function ログインユーザーは書籍が紐づいていないジャンルを削除できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /** @test */
    public function 書籍が紐づいているジャンルは削除できない()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ジャンルを紐付け
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');

        // 削除されていないこと
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
