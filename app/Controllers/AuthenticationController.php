<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthenticationController extends Controller
{
    public function new(): void
    {
        $title = 'Login';
        $this->render('auth/new', compact('title'));
    }

    public function authenticate(Request $request): void {
        $params = $request->getParam('users');
        if (!is_array($params)) {
            $params = [
                'email' => $request->getParam('email') ?? null,
                'password' => $request->getParam('password') ?? null,
            ];
        }

        $email = trim((string)($params['email'] ?? ''));
        $password = $params['password'] ?? '';

        if ($email === '' || $password === '') {
            FlashMessage::danger('Por favor preencha e-mail e senha.');
            $this->redirectTo(route('users.login'));
            return;
        }

        $user = User::findByEmail($email);

        if ($user && $user->authenticate($password)) {
            Auth::login($user);
            FlashMessage::success('Login realizado com sucesso!');
        }else{
            FlashMessage::danger('E-mail e/ou senha inválidos!');
            $this->redirectTo(route('users.login'));
        }

        FlashMessage::danger('Credenciais inválidas.');
        $this->redirectTo(route('users.login'));
    }

    public function destroy(): void {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso!');
        $this->redirectTo(route('users.login'));
    }
}
