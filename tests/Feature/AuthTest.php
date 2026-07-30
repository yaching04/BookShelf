<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録画面が表示できる()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /** @test */
    public function ログイン画面が表示できる()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function 会員登録ができる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後にリダイレクトされること
        $response->assertRedirect('/books');

        // ユーザーがDBに保存されていること
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function ログインができる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/books');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function ログアウトができる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/books');
        $this->assertGuest();
    }

    /** @test */
    public function 会員登録で名前が未入力の場合はエラーになる()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください。',
        ]);
    }

    /** @test */
    public function 会員登録でメールが未入力の場合はエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください。',
        ]);
    }

    /** @test */
    public function 会員登録でパスワードが未入力の場合はエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください。',
        ]);
    }

    /** @test */
    public function 会員登録でパスワード確認が一致しない場合はエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワード（確認用）と一致しません。',
        ]);
    }
}
