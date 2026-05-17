<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class SecretariesController extends Controller
{
    public function index(): void
    {
        $this->layout = 'application';
        $title = 'Home - Secretaria';
        $this->render('secretary/index', compact('title'));
    }
}
