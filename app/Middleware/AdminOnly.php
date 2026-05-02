<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AdminOnly implements Middleware
{
    public function handle(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            FlashMessage::danger('Acesso restrito a administradores.');
            header('Location: ' . route('auth.check'));
            exit;
        }
    }
}
