<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class DoctorOnly implements Middleware
{
    public function handle(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isDoctor()) {
            FlashMessage::danger('Acesso restrito a médicos.');
            header('Location: ' . route('auth.check'));
            exit;
        }
    }
}
