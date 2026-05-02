<?php

namespace Tests\Unit\Lib\Authentication;

use Lib\Authentication\Auth;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private User $user;
    private User $user2;

    public function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        
        $this->user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'cpf' => '12345678901',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();

        $this->user2 = new User([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'cpf' => '98765432101',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user2->save();
    }

    public function tearDown(): void
    {
        parent::setUp();
        $_SESSION = [];
        \Lib\Authentication\Auth::logout();
    }

    public function test_login_sets_session_user_id(): void
    {
        Auth::login($this->user);

        $this->assertEquals(1, $_SESSION['user']['id']);
    }

    public function test_login_sets_session_as_array(): void
    {
        Auth::login($this->user);

        $this->assertIsArray($_SESSION['user']);
        $this->assertArrayHasKey('id', $_SESSION['user']);
    }

    public function test_user_returns_user_from_session(): void
    {
        Auth::login($this->user);

        $userFromSession = Auth::user();

        $this->assertEquals($this->user->id, $userFromSession->id);
        $this->assertEquals($this->user->email, $userFromSession->email);
    }

    public function test_user_returns_null_when_not_logged_in(): void
    {
        $user = Auth::user();

        $this->assertNull($user);
    }

    public function test_check_returns_true_when_logged_in(): void
    {
        Auth::login($this->user);

        $this->assertTrue(Auth::check());
    }

    public function test_check_returns_false_when_not_logged_in(): void
    {
        $this->assertFalse(Auth::check());
    }

    public function test_logout_clears_session(): void
    {
        Auth::login($this->user);
        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertFalse(isset($_SESSION['user']));
    }

    public function test_logout_empties_session_array(): void
    {
        Auth::login($this->user);
        Auth::logout();

        $this->assertEmpty($_SESSION);
    }

    public function test_login_with_different_users(): void
    {
        Auth::login($this->user);
        $this->assertEquals(1, $_SESSION['user']['id']);

        Auth::logout();
        $this->assertEmpty($_SESSION);

        Auth::login($this->user2);
        $this->assertEquals(2, $_SESSION['user']['id']);
    }

    public function test_user_caching_on_multiple_calls(): void
    {
        Auth::login($this->user);

        $user1 = Auth::user();
        $user2 = Auth::user();

        $this->assertSame($user1, $user2);
    }

    public function test_logout_clears_user_cache(): void
    {
        Auth::login($this->user);
        $user1 = Auth::user();
        $this->assertNotNull($user1);

        Auth::logout();

        $user2 = Auth::user();
        $this->assertNull($user2);
    }
}
