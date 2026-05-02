<?php

use App\Controllers\AdminsController;
use App\Controllers\AuthenticationsController;
use App\Controllers\DoctorsController;
use App\Controllers\PatientsController;
use App\Controllers\SecretariesController;
use Core\Router\Route;

// Authentication
Route::get('/', [AuthenticationsController::class, 'checkLogin'])->name('auth.check');
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');
    
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminsController::class, 'index'])->name('admin.index');
    });

    Route::middleware('doctor')->group(function () {
        Route::get('/doctor', [ DoctorsController::class, 'index'])->name('doctor.index');
    });

    Route::middleware('secretary')->group(function(){
        Route::get('/secretary', [ SecretariesController::class, 'index'])->name('secretary.index');
    });

    Route::middleware('patient')->group(function(){
        Route::get('/patient', [ PatientsController::class, 'index'])->name('patient.index');
    });

});


