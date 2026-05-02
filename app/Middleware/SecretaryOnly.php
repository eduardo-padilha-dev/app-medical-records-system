<?php
namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class SecretaryOnly implements Middleware
{
    public function handle(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isSecretary()) {
            FlashMessage::danger('Acesso restrito a secretarias.');
            header('Location: ' . route('auth.check'));
            exit;
        }
    }
}