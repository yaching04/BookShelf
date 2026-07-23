<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_with_invalid_credentials_shows_error_message(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }
}
