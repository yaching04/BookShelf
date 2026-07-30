<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストでもランキング画面を表示できる()
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }

    /** @test */
    public function ログインユーザーもランキング画面を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ranking');

        $response->assertStatus(200);
    }

    /** @test */
    public function レビューがある本がランキングに表示される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'ランキングテスト本',
        ]);

        // レビューを投稿（平均評価を作る）
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating'  => 5,
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertSee('ランキングテスト本');
    }

    /** @test */
    public function レビューが0件の本はランキングに表示されない()
    {
        $book = Book::factory()->create([
            'title' => 'レビューなしの本',
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertDontSee('レビューなしの本');
    }
}
