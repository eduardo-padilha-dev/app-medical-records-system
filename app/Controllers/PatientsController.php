<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class PatientsController extends Controller
{
    public function index(): void
    {
        $this->layout = 'application';
        $title = 'Home - Paciente';
        $this->render('patient/index', compact('title'));
    }
}
