<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class AuthenticationController extends Controller
{
    public function login(): void
    {
        $title = 'Login';
        $this->render('auth/login', compact('title'));
    }
}
