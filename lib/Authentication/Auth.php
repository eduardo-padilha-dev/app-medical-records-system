<?php

namespace Lib\Authentication;

use App\Models\User;

class Auth
{
    private static ?User $cachedUser = null;

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function login(User $user): void
    {
        self::ensureSession();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user->id];
        self::$cachedUser = $user;
    }

    public static function user(): ?User
    {
        self::ensureSession();

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        if (isset($_SESSION['user']['id'])) {
            $id = (int) $_SESSION['user']['id'];
            self::$cachedUser = User::findById($id);
            return self::$cachedUser;
        }

        return null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']['id']) && self::user() !== null;
    }

    public static function logout(): void
    {
        self::ensureSession();
        $_SESSION = [];
        self::$cachedUser = null;
        session_destroy();
    }
}
