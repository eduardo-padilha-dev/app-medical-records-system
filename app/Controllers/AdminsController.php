<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class AdminsController extends Controller
{
    public function index(): void
    {
        $this->layout = 'application';
        $title = 'Home - Administrador';
        $this->render('admin/index', compact('title'));
    }
}
