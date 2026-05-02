<?php
namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class EmployeesOnly implements Middleware
{
    public function handle(Request $request): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isDoctor() && !$user->isSecretary())) {
            FlashMessage::danger('Acesso restrito a funcionários.');
            header('Location: ' . route('root'));
            exit;
        }
    }
}