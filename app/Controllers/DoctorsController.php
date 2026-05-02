<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class DoctorsController extends Controller
{
    public function index(): void
    {
        $this->layout = 'application';
        $title = 'Home - Medico';
        $this->render('doctor/index', compact('title'));
    }
}