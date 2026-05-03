<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class PatientOnly implements Middleware
{
    public function handle(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isPatient()) {
            FlashMessage::danger('Acesso restrito a pacientes.');
            header('Location: ' . route('auth.check'));
            exit;
        }
    }
}
