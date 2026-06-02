<?php

use App\Controllers\AdminsController;
use App\Controllers\AuthenticationsController;
use App\Controllers\DoctorsController;
use App\Controllers\MedicalRecordsController;
use App\Controllers\PatientsController;
use App\Controllers\SecretariesController;
use Core\Router\Route;
use App\Controllers\AppointmentsController;
use App\Controllers\ExamsController;

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

        Route::get('/medical_records/new', [MedicalRecordsController::class, 'new'])
            ->name('medical_records.new');

        Route::post('/medical_records', [MedicalRecordsController::class, 'create'])
            ->name('medical_records.create');

        Route::get('/medical_records/{id}/edit', [MedicalRecordsController::class, 'edit'])
            ->name('medical_records.edit');

        Route::put('/medical_records/{id}', [MedicalRecordsController::class, 'update'])
            ->name('medical_records.update');

        Route::delete('/medical_records/{id}', [MedicalRecordsController::class, 'destroy'])
            ->name('medical_records.destroy');
    });

    Route::middleware('secretary')->group(function () {
        Route::get('/secretary', [SecretariesController::class, 'index'])->name('secretary.index');

        Route::get('/appointments/new', [AppointmentsController::class, 'new'])
            ->name('appointments.new');

        Route::post('/appointments', [AppointmentsController::class, 'create'])
            ->name('appointments.create');

        Route::get('/appointments/{id}/edit', [AppointmentsController::class, 'edit'])
            ->name('appointments.edit');

        Route::put('/appointments/{id}', [AppointmentsController::class, 'update'])
            ->name('appointments.update');

        Route::delete('/appointments/{id}', [AppointmentsController::class, 'destroy'])
            ->name('appointments.destroy');

        Route::get('/exams/new', [ExamsController::class, 'new'])
            ->name('exams.new');

        Route::post('/exams', [ExamsController::class, 'create'])
            ->name('exams.create');

        Route::delete('/exams/{id}', [ExamsController::class, 'destroy'])
            ->name('exams.destroy');
    });

    Route::middleware('patient')->group(function () {
        Route::get('/patient', [PatientsController::class, 'index'])->name('patient.index');
    });

    Route::get('/medical_records', [MedicalRecordsController::class, 'index'])
        ->name('medical_records.index');

    Route::get('/medical_records/page/{page}', [MedicalRecordsController::class, 'paginate'])
        ->name('medical_records.paginate');

    Route::get('/medical_records/{id}', [MedicalRecordsController::class, 'show'])
        ->name('medical_records.show');

    Route::get('/appointments', [AppointmentsController::class, 'index'])
        ->name('appointments.index');

    Route::get('/appointments/{id}', [AppointmentsController::class, 'show'])
        ->name('appointments.show');

    Route::get('/exams', [ExamsController::class, 'index'])
        ->name('exams.index');

    Route::get('/exams/{id}', [ExamsController::class, 'show'])
        ->name('exams.show');
});
