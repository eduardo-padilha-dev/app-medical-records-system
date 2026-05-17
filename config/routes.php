<?php

use App\Controllers\AdminsController;
use App\Controllers\AuthenticationsController;
use App\Controllers\DoctorsController;
use App\Controllers\MedicalRecordsController;
use App\Controllers\PatientsController;
use App\Controllers\SecretariesController;
use Core\Router\Route;

// Authentication
Route::get('/', [AuthenticationsController::class, 'checkLogin'])->name('auth.check');
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.authenticate');

Route::middleware('auth')->group(function () {
    Route::delete('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminsController::class, 'index'])->name('admin.index');
    });

    Route::middleware('doctor')->group(function () {
        Route::get('/doctor', [DoctorsController::class, 'index'])->name('doctor.index');
    });

    Route::middleware('secretary')->group(function () {
        Route::get('/secretary', [SecretariesController::class, 'index'])->name('secretary.index');
    });

    Route::middleware('patient')->group(function () {
        Route::get('/patient', [PatientsController::class, 'index'])->name('patient.index');
    });

    // ---------------------------------------------------------------
    // CRUD de Prontuário Médico
    //
    // Nota sobre acesso:
    //   - Médicos: podem listar, criar, editar e excluir os seus prontuários.
    //   - Pacientes: podem listar e visualizar os seus próprios prontuários.
    //   - A verificação de quem pode fazer o quê é feita dentro do controller
    //     para evitar duplicar middleware e permitir a rota compartilhada.
    // ---------------------------------------------------------------

    // Atenção: a rota /new precisa vir ANTES de /{id} para que o Router
    // não tente interpretar "new" como um inteiro (parâmetro de ID).
    Route::get('/medical_records/new', [MedicalRecordsController::class, 'new'])
        ->name('medical_records.new');

    Route::get('/medical_records', [MedicalRecordsController::class, 'index'])
        ->name('medical_records.index');

    Route::post('/medical_records', [MedicalRecordsController::class, 'create'])
        ->name('medical_records.create');

    Route::get('/medical_records/{id}', [MedicalRecordsController::class, 'show'])
        ->name('medical_records.show');

    Route::get('/medical_records/{id}/edit', [MedicalRecordsController::class, 'edit'])
        ->name('medical_records.edit');

    Route::put('/medical_records/{id}', [MedicalRecordsController::class, 'update'])
        ->name('medical_records.update');

    Route::delete('/medical_records/{id}', [MedicalRecordsController::class, 'destroy'])
        ->name('medical_records.destroy');
});
